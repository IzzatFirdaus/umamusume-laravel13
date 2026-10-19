# Race Calendar, Slice Research, and Session Consolidation

## Provenance

The following 7 files were embedded verbatim from docs/design-research/:

1. RACE-CALENDAR-GAPS.md (352 lines)
2. HANDOFF-RACE-READ-PATH-2026-09-29.md (80 lines)
3. slice-4-preflight-2026-10-01.md (123 lines)
4. slice-5-phase-a-best-for-2026-10-01.md (177 lines)
5. slice-6-currency-2026-10-01.md (248 lines)
6. slice-7-prd-revision-draft-2026-10-01.md (156 lines)
7. SESSION-CONSOLIDATION-2026-09-30.md (307 lines)

**Note, corrected 2026-10-03:** this file previously read that `global-race-sources.md` "does NOT exist
anywhere in the repository. It is omitted." That was true when written and stopped being true when
Round 5 promoted the file out of the gitignored root `research-scratch/`. It is embedded below as
section `global-race-sources.md`, 225 lines, 40 em-dashes, 4 code fences.

**Round 7 (2026-10-03), one more source and one bound companion:**

1. `global-race-sources.md` (225 lines), promoted from root `research-scratch/` in Round 5 and
   embedded here rather than left as a single-topic master of its own.

`calendar-tables.md` is **not** embedded and deliberately stays at
`docs/research-scratch/calendar-tables.md`. It is the generated data companion of this master: 400+
rows of per-scenario turn tables with no markdown headings, rendered by
`research-scratch/scripts/gen_calendar.py` and re-synced in place by `research-scratch/scripts/resync_doc.py`,
which matches its `@@Junior Year — 58 rows`-style markers. Both scripts live in the gitignored root
`research-scratch/`, so moving the tables would break a documented pipeline whose fix cannot be
committed. Cite this master for the calendar's reasoning and `calendar-tables.md` for the rows.

> **Reversed 2026-10-04 (Round 11, owner ruling).** The paragraph above stands as the Round 7 record.
> The owner has since ruled to fold the tables and commit the generators, so the rows are now the
> `## calendar-tables.md` section at the end of this file and the two scripts are tracked at
> `tools/gen_calendar.py` and `tools/resync_doc.py`. The carrier the re-sync reads is this section, not
> a standalone file. Measured at the fold: 512 of that file's 551 lines were already byte-identical
> inside `docs/scenarios/09-global-race-calendar.md`, so the claim above that nothing in it was
> redundant was wrong; only the `Unique races, base pool vs additions` block existed nowhere else.
> `python tools/resync_doc.py` run against the folded section reports `unchanged` for all seven pushed
> labels and leaves the scenario guide byte-identical.

**Round 11 (2026-10-04), two more sources:**

1. `unity-cup-capture.md` (153 lines, 11 headings), from `docs/proposals/`. The `turn_entries` and
   `scenario_slots` schema proposal that `SCREEN_SPEC.md` §7-6 was waiting on. It is still a proposal:
   not authorized, no migration written, no column created, and its six owner questions stay open in
   that section, which is now the authoritative copy.
2. `calendar-tables.md` (551 lines, eight `@@`-marked blocks, no markdown headings), embedded verbatim
   with nothing demoted because there are no headings, per the reversal above.

Internal headings have been demoted by one level. All content is preserved verbatim.

**Round 14 (2026-10-10, augment-only):** `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` (776 lines), the
tracked `[Global]` client capture note behind the Rice Shower / Unity Cup audit walks, embedded verbatim
as the section `## [Rosy_Dreams]Rice_Shower_Unity-Cup.md` at the end of this file. The source stays in
place as the live capture; nothing was deleted and no citation was repointed.

---

## RACE-CALENDAR-GAPS.md

## Race calendar — gaps and open questions

**Date:** 2026-09-29
**Scope:** what the race read path does **not** know, what it knows twice, and what it was
asked not to decide.
**Not a to-do list.** Several entries are deliberately unresolved because resolving them is
either an owner decision or another slice's call site. "Owner" below names who can close it,
not who will.

Companion documents: `docs/scenarios/09-global-race-calendar.md` (the data and its evidence),
`docs/design-research/HANDOFF-RACE-READ-PATH-2026-09-29.md` (why the read path moved).

---

### 1. Two tables both hold goal races

**Missing:** one home for the career race schedule.

`race_catalog_slots` holds 410 rows keyed on `(scenario_key nullable, year, month, half, title)`.
`scenario_slots` also holds `goal_race` rows — 296 of them seeded by `f0ae288` — keyed on
`(scenario_key, month, half, kind, source_key)` after `c0a743f` widened it so several goal races
could share a half-month.

**Why not resolved here:** the two threads reached the same problem from different directions and
each fixed the half it could see. `c0a743f` solved insert collision; `82959e9` moved the read path.
Reconciling means deleting one side's rows, and neither session can judge the other's scope. The
retarget note exists precisely so this is a known state rather than a surprise.

**Update, `37ed6c2`:** the picker moved onto `race_catalog_slots` too, so nothing in the read path
renders a seeded `goal_race` row any more. The rows are still seeded and still wrong-by-duplication;
what changed is that they are now inert rather than user-visible. `free_race` rows remain a genuine
`scenario_slots` concern — those have no catalogue row by definition, and the picker keeps a separate
control for them.

**Needed to resolve:** decide which table the seeder writes, then drop the other's `goal_race`
rows and remove the kind from `ScenarioSlot::VALID_KINDS`. `scenario_slots` still needs
`team_race`, `grade_deadline`, `scripted_event` and `free_race` regardless — those are correctly
its, and `free_race` in particular has no catalogue row by definition.

**Owner:** Slice 11 / 12, with the read path's owner. Neither table's columns were removed by
this pass, so the reconciliation can go either way without unwinding work.

---

### 2. Tier labels disagree between the seeder and the parser

**Missing:** one standard for whether evidence outside the export counts as a label source.

`races.json` carries a numeric `grade` and **no label field** — that part is correct and
uncontested. From it, R65 concluded codes 200/300/700 have no source and made
`ScenarioSlotSeeder` write `tier = null` for them.

`GametoraRaceCatalogParser` maps all five codes, because two publishers outside the export name
them: uma.guide's dataset stores `grade` and `gradeName` together
(`{"raceName":"Daily Hai Junior Stakes","grade":200,"gradeName":"G2"}`), and Game8's all-races
table independently prints `G2` for that race and `G3` for Artemis Stakes. `09` §"Tier labels"
records the full chain including the 12-cell distance-band match, which is what rules out a
swapped 200/300 mapping rather than merely preferring one.

**Why not resolved here:** this is a standards question, not a data question, and it was not mine
to settle. The parser's comment states the evidence and names the disagreement at the map itself,
where someone editing it will meet it.

**Consequence today:** the two tables render different tier coverage for the same race. It does
**not** affect the read path — the grid and picker read `race_catalog_slots`.

**Needed to resolve:** either accept out-of-export sources and restore the seeder's labels, or
rule that only the export counts and null the parser's too. The second is self-consistent but
throws away `G2`/`G3`/`Pre-OP` that two independent publishers assert.

**Owner:** the owner, as a sourcing standard.

---

### 3. The maiden rule is global in the source and per-row in the schema

**Missing:** a faithful encoding of when a race is maiden-locked.

The export states the rule once, on the maiden row: *"You can't participate in any races listed
here until you win either Debut or any of the Maiden Races."* That is a statement about every
standard race, not a property of individual rows. The schema models it as `is_maiden_gated` per
row, and both `f0ae288` and the parser set it `false` everywhere — so `maiden_locked` never fires
in production data.

**Why not resolved here:** the parser setting it `true` for all standard races would be asserting
a per-row fact the source does not encode per row, and it would change rendering for every winless
run. `RaceSlotPanelComposerTest` covers the state, so deleting the column would have removed
tested behaviour instead.

**Status after `c320fc4`:** documented dead state, not a bug. The dashed outline and its sentence
stay in the component with a comment naming the parser line that makes it unreachable, so the
treatment survives until a source can drive it.

**Needed to resolve:** a product decision — either the column means "this race is maiden-gated"
and is populated true for standard races, or the rule is a run-level state and the column should
go away in favour of one check.

**Owner:** whoever owns the maiden gate as a UI concept.

---

### 4. `careerYearForTurn()` is an inference, and the grid depends on it

**Missing:** a stored career year.

`turn_entries.turn` is a single monotonic counter — `nextTurn()` is `max(turn) + 1`, there is no
per-year reset, and no cap on it exists anywhere in the codebase. So the year is derived:
`min(3, max(1, intdiv(turn - 1, 24) + 1))`.

The 24 is not a guess: it is the client's own grid, twelve months times Early and Late,
corroborated against `[Global]` captures in `09`, where the debut at turn 12 lands on Late June of
Junior year. But the derivation assumes a career is exactly three years of exactly 24 turns with no
skipped or extra turns, and nothing in the schema enforces that.

**Why it matters:** if the derivation is wrong anywhere, the grid silently shows the wrong year's
races for a run — a wrong calendar is worse than a missing one, because it looks authoritative.

**Needed to resolve:** either store the year on `turn_entries` at log time, or pin the derivation
with a test against a real long-career capture. `RaceCalendarYearTabsTest` covers turn→year
arithmetic including the clamp, which is the second option; it cannot substitute for the first.

**Owner:** the schema's owner. This is a column decision, not a read-path one.

---

### 5. Track names and first-place fan figures are not resolvable

**Verified this pass, and it is a real gap:** `config('uma.sources')` declares exactly two
sources — `gametora-characters` and `gametora-race-catalog`. **Neither `racetracks_extended` nor
`en/race-fans` is declared**, so neither can be fetched through the engine.

Consequences in `race_catalog_slots`:

- `track_id` is stored as the export's numeric id (e.g. `10008`). No track **name** is available.
  `racetracks_extended` has it, and `09`'s tables were built from it.
- `fans_gain_curve` is stored as the curve id. No **payout figure** is available. `en/race-fans`
  maps curve → fans by finishing position, and it is where the nine unresolvable slots were
  identified (curves 51–56 have no `[Global]` row).

**Why not resolved here:** each needs its own declared source entry, which is the owner gate at
`config/uma.php:31`, and the parser contract takes one body per source so joining three datasets
needs a design the pipeline does not have yet.

**Measured against the populated catalogue** (410 rows from the `294424fc…` snapshot, 2026-09-29): eight
rows carry a null `fans_gain_curve`, and the same eight carry a null `fans_needed` — Junior Make Debut
(turn 12), Junior Maiden Race (turn 13), and the six year-4 finale rows, which sit outside the 24-cell
grid. The **seven** regional ⚠️ slots `09` names are **not** among them: each stores a curve id (51–54)
and it is the payout that `en/race-fans` would have to resolve, which is this item's gap rather than a
null on the row. The two Longchamp rows `09` also marks ⚠️ are not in the corpus at all (`Prix Niel`,
`Prix Foy`: 0 rows), which is correct — they never reached `[Global]`. So the grid can hold exactly two
cells whose fan figure must render as nothing rather than `0`, and "nine slots render null fan-gain" is
true of the document's tables and not of the rendered grid.

**Needed to resolve:** two more `uma.sources` entries plus either a join step in the persister or
lookup tables populated from them. Until then the picker shows distance, surface, tier and fan
gate — all of which are in `race_instances` — but not the venue name.

**Owner:** Data Engineer scope, with the owner's approval on the two new sources.

---

### 6. `trainee_goals`, and the picker that no longer waits on it

**Missing:** any record of a character's objectives. The picker half of this entry is **built**
(`37ed6c2`), and it stays filed here because the `trainee_goals` shape below was derived while
solving it and must not be re-derived by the next reader.

**Correction, recorded rather than edited away:** this entry stated that `KNOWN-ISSUES.md` still
listed **KI-21 as OPEN** while `6c1969f` appeared to have implemented its second option. Re-checked
on 2026-09-29 before the picker was touched: **KI-21 is CLOSED** (Slice 13, R67, `6c1969f` —
server-driven disclosure, no Alpine dependency), and `race-panel.blade.php` carries the comment that
proves it. The gate was real; the register entry was stale.

**What `37ed6c2` built:** the calendar branch of the race form reads `race_catalog_slots` through
`TrainingRun::calendarRaceSlots()`, scoped to the career year the screen is showing, because
`scenario_slots` carries no year and used to offer every seeded race on whatever tab was open. Each
option names its half-month and grade, and a picked race records against `race_catalog_slot_id` so
the cell it fills is findable again. A race the Trainer typed earlier keeps its own control and its
`scenario_slot_id` link; an entry naming both is refused.

**Proposed `trainee_goals` shape, recorded so nobody re-derives it:**

```text
trainee_goals
├── umamusume_id          FK — Goals are per character, not per card. training_runs FKs
│                         umamusume_id, and the four client panels in 09 show Goals
│                         differing per trainee, not per costume variant.
├── training_run_id       FK nullable — set when scoped to one run.
├── goal_type             race | fan
├── race_catalog_slot_id  FK nullable — race goals only. Points at race_catalog_slots,
│                         which carries year, so (year, turn) resolves to a real row.
├── year                  junior | classic | senior — deadline year for fan goals.
├── turn                  int nullable — deadline turn for fan goals.
├── required_placement    string nullable — '1st' | 'top 3' | 'top 5' | 'higher than 5th'
│                         | free text. Race goals only, and per CHARACTER: the same race
│                         carries different placement text for different trainees.
├── fan_threshold         int nullable — fan goals only. No slot FK: a fan goal is not
│                         a race and must not be forced into one.
└── notes, timestamps
```

Two kinds, and they are genuinely different: a **race goal** is a specific race with a placement
requirement; a **fan goal** is a threshold by a deadline and occupies no grid cell.

**Needed to resolve:** per-character Goal and placement text captured from client panels or guide
transcription — **none of it is in the export**, which is why only four characters have any Goal data
at all.

**Owner:** this thread, once the Goal data exists. No longer blocked on KI-21.

---

### 7. The Goal pennant has no source

**Resolved as a defect, still open as a feature.** `79ffad5` stopped drawing the pennant from
`is_mandatory`, which was the audit's conflation: a career obligation is not a character's
objective. The component still renders a `goal` cell with its D-181 treatment, so the fix is a
wiring change and not a rewrite.

**What is missing:** nothing can currently set the state, because `trainee_goals` does not exist.
The grid therefore shows no pennants at all.

**Status after `c320fc4`:** documented dead state, not a bug, and the markup is deliberately kept.
The component's state comment names `79ffad5` and this entry so the D-181 treatment is not deleted
by a reader who assumes an unreachable branch is dead code.

**One correction on the evidence, because it was asserted the other way:** that the client shows a
red banner on the debut, qualifier, semifinal and final is **not supported by the capture corpus**.
Four panels were read cell by cell for `09`; every banner in them sits on a per-character race —
NHK Mile Cup, Tokyo Yushun, Kikuka Sho, Tenno Sho (Autumn), Nikkei Sho, Takarazuka Kinen, Arima
Kinen — and none on the debut or the final rounds. The change is safe under either reading, since
the treatment is kept and only the model's authority to assert it was withdrawn.

**Needed to resolve:** item 6.

**Owner:** this thread, once `trainee_goals` exists.

---

### 8. `SourceFetcher` writes `.html` for JSON snapshots

**Pre-existing, inherited by the new source, not introduced here.** Every row's `snapshot_path`
ends in `.html` — for example
`snapshots/gametora-race-catalog/2026-09-28/294424fc78e0058b…a3a24791.html` — for a body fetched as
`application/json`.

**Why not fixed here:** it is engine-level, shared with `gametora-characters`, and changing it
alters paths already recorded on rows. Out of this pass's surface.

**Needed to resolve:** derive the extension from the response content type, and decide whether
existing recorded paths get rewritten or grandfathered.

**Owner:** pipeline / engine cleanup.

---

### 9. A check that cannot fail for the reason it claims

**Partly resolved, kept as a pattern.** The `RaceCalendarTest` case that labels manual rows
distinctly asserts the string `Trainer-entered` appears in the rendered HTML, but it **hand-builds**
the cells array and passes it to the component — it never calls `calendarCells()`. So the model could
stop emitting `manual` entirely and that test would stay green. Two independent sessions reached this
conclusion: `5dcc06c` added model-path coverage, and Slice 13's `cb9b61f` named the same flaw in its
own comment.

(The entry used to cite that test by line number. Two commits in this slice moved the lines, which
is the argument for naming a test instead of numbering it.)

The same class of error caused a false report earlier in this pass: a verification script reused one
Laravel query builder across a year loop, and because `Builder::where()` mutates in place, every
later count was scoped by the earlier one. It reported "zero rows for years 2 and 3" against
perfectly good data. That trap is now a comment in `RaceCatalogSlotTest`'s header.

**Needed to resolve:** none — recorded so the next report of breakage asks "would a test have
caught it?" before assuming the code is fine or the report is wrong.

**Owner:** nobody. It is a note about how to read the entries above it.

---

### 10. Two identical free races sit in the development database

**What is there.** `scenario_slots` 297 and 298 are the same row twice: `kind = free_race`,
`scenario_key = ura_finale`, `title = 'Naruta Kinpa Cup'`, `tier = 'G3'`, `month = 5`, `half = 'Early'`,
`is_manual = 1`, with no `source_url` and no `source_key`. `race_entries` #1 points at 298. The grid
draws Early May as a two-race cell, and its accessible name reads
`"Early May: Entry open, 2 races: Naruta Kinpa Cup, Naruta Kinpa Cup; one entered"`.

**Why this is not a code defect.** The name is a design-prototype sample
(`docs/design-research/prototypes/screen-a-scenario-v10.html:489`), not a race in the export or in `09`,
and the `G3` was typed into the manual form rather than derived — which is why a diagnosis that read it
as the catalogue mislabelling a Kimpai race came out the other way. Both Kimpai races are G3 in every
source this repository holds; the G2 in that cluster is Nikkei Shinshun Hai.

**What it does raise.** Writing the same free race twice is what the manual path does when it is used
twice, so the open question is whether the manual path should adopt an identical existing row instead of
creating a second one. That is a product call about `scenario_slots`, which this slice was told to leave
alone, and it is recorded rather than decided.

**Owner:** the owner, as a data decision.

---

### 11. `tier_override` is validated and then dropped on the floor

**Found while adding the catalogue link to the same validator (`37ed6c2`); not introduced
there.** `StoreRaceEntryRequest` accepts `tier_override` (nullable string, max 10) on the
calendar branch and `prepareForValidation()` blanks an empty submission to null. The
controller's calendar branch then intersects the validated payload with a key list that does
not name `tier_override`, so the field reaches no write. It is accepted, normalised, and
discarded.

**What each reading costs.** As a stub awaiting follow-through, the missing piece is a place
for an entered tier to live: `race_entries` has no tier column, and a tier the Trainer states
would have to override `race_catalog_slots.tier` **per entry** rather than rewrite a shared
catalogue row that prices other runs. As dead code, it is one validation rule and one
blanking line.

**Why it is filed rather than deleted here:** no test asserts the field has an effect.
`FreeRaceWriterTest` posts `tier_override` and then asserts the placement landed — it never
reads the tier back, so the test passes whether or not anything stores it. That is item 9's
pattern one more time, and deleting the rule on the strength of a test that cannot see it
would be a guess dressed as a cleanup.

**Needed to resolve:** decide stub-or-dead. Stub means a column plus a test that reads the
entered tier back off the entry; dead means both lines go.

**Owner:** whoever owns the race form's fields.

---

### 12. `data_sources` is per-character provenance, so the catalogue writing no row there is correct

**Recorded so nobody "fixes" this.** An empty `data_sources` table looked like a missing write
while the catalogue was being populated. It is not a missing write, and it is not a fetch log.

The table is keyed on `foreignId('umamusume_id')->constrained('umamusume')->cascadeOnDelete()`
(`2026_09_26_162819_create_data_sources_table.php`), and its only writer is
`PromoteMatchedRecord`, which creates one row per source that contributed to a **promoted
character record**. It is provenance attached to an entity — which is also why
`Umamusume::dataSources()` is a `hasMany`.

The race catalogue has no match or promote stage (ADR-0003 R3's reference dispatch bypasses
both) and no owning `umamusume` row to attach to, so it carries provenance on its own rows
instead: `source_key`, `source_url`, `snapshot_path`, `fetched_at`, `source_timezone`. After
the fill pass all five are present on **410 of 410** rows.

**Consequence for the next reader:** on a development database, `data_sources` being empty
says nothing about whether a fetch ran. The proof a fetch ran is `race_catalog_slots`'s row
count, the `snapshot_path` on those rows, and the file on the snapshots disk. A per-fetch write
into this table would need the table reshaped away from the `umamusume_id` foreign key, which
is a schema decision and not a gap to close quietly.

---

## HANDOFF-RACE-READ-PATH-2026-09-29.md

## Handoff — race read path moves to `race_catalog_slots`

**Date:** 2026-09-29
**Author:** the calendar/parser thread (commits `c111049`, `ef75ff0`, `e7b78a4`)
**Audience:** Slice 11 (`c0a743f`, `f0ae288`, `5820e77`, `70248b3`, `65f8b92`, `4992282`) and whoever next touches the run screen
**Status:** notice, not a request. The retarget lands as its own commit; this file is the reason it exists.

### Why this note exists

Three collisions in one pass, all real, none hostile:

| Time   | Slice 11                                                                                                         | This thread                                                     |
| ------ | ---------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------- |
| —      | `c0a743f` widens `scenario_slots`' unique key so several goal races share a half-month                           | audit had reported that exact collision (406 slots → 24 keys)   |
| —      | `f0ae288` seeds goal races into `scenario_slots` from committed copies of `race_instances`/`races`/`ura-races`   | `c111049` parses the same three exports into a new table        |
| —      | `70248b3` fixes the cell-clobber in `calendarCells()` and the blade                                              | was step 6 of this thread's brief                               |

Both threads solved the same problem in different tables. This note records which one the read path uses and why, so nobody reconstructs the timeline from commit order.

### The deciding fact

`scenario_slots` columns today:

```text
id, scenario_key, kind, slot_label, title, description, month, half, tier,
fans_needed, is_mandatory, is_maiden_gated, sort_order,
source_url, snapshot_path, fetched_at, source_timezone, is_manual, timestamps
```

It has **no `year`**. `c0a743f` added `source_key`, which makes several goal races legal in one half-month, but a Classic Early-May slot and a Senior Early-May slot remain indistinguishable — and `09` records **14 G1s that recur across Classic and Senior at the identical month and half**. Fourteen races the grid must place in two years with nothing in the row saying which year it is.

It also has **no `distance`, `surface`, `track`, or payout curve**, so the picker has nothing to show per row, and `f0ae288` skips `month === 99999` outright, so **the four scenario finals are not in it at all**.

`race_catalog_slots` has all of it and is populated.

### State of the populated table

`uma:fetch gametora-race-catalog` against a throwaway database, 2026-09-29:

```text
'gametora-race-catalog': 0 updated, 410 created, 0 skipped (manual), 0 to review.
```

410 rows: Junior 58, Classic 161, Senior 185, finale block 6. That is `docs/scenarios/09-global-race-calendar.md`'s 413 slots minus Prix Niel, Prix Foy (`unreleased_servers: ['en']`) and `final_masters` (`[JP-Only]`, no `start_en`). `scenario_key` is set on exactly 4 rows — `ura_finale`, `unity_cup`, `trackblazer`, `grand_concert` — and null on the other 406, because the scenarios share every monthly slot and differ only at the final. All 410 carry `source_url`, `snapshot_path`, `fetched_at` and `source_timezone`, per ADR-0003 R3.

### What changes

`calendarCells()`, `race-calendar.blade.php`, `race-panel.blade.php` and their tests read `race_catalog_slots`, keyed on `(year, month, half)`. **`70248b3`'s multi-slot aggregation is kept as written** — it was the right fix for the symptom it addressed, and it is the shape the new table needs too. Only the source of the rows moves.

### What does not change

- **`scenario_slots` is untouched.** No column dropped, no index changed.
- **`source_key` stays.** Unused by the read path, still used by `f0ae288`.
- **`goal_race` stays in `VALID_KINDS`.** Removing it is deferred; see below.
- **`free_race`, `team_race`, `grade_deadline`, `scripted_event` stay and stay on `scenario_slots`.** They belong there. A Trainer-chosen free race has no catalogue row by definition, so `free_race` is correctly Slice 11's kind and this thread will not move it.
- **`ScenarioSlotSeeder` and the committed export files stay.**

### Known overlap, deliberately left

Both tables can hold a goal race today. That is not resolved here — resolving it means either deleting `f0ae288`'s seeded rows or dropping `race_catalog_slots`, and neither is a call one of the two threads gets to make alone. Filed as a gap with both hashes cited.

### Deferred: `isMandatoryGoal()`

`ScenarioSlot::isMandatoryGoal()` returns `isGoalRace() && $this->is_mandatory`, and `TrainingRun::calendarCell()` uses it to draw the red pennant. So the pennant currently marks **scenario-scoped** mandatory races — debut and finals — as if they were per-character Goals. `09` establishes from four client panels that Goals are per-trainee and that only the debut and the final are universally mandatory.

The fix is small: decouple the two, draw the pennant from a goals table, and leave `is_mandatory` as the honest but separate fact it is. It touches `ScenarioSlot.php`, which `c0a743f` modified and which drives the pennant `f0ae288` now feeds.

**If Slice 11 is still active, this is yours — say so and this thread will not touch it.** If Slice 11 has closed, this thread takes it with two-state tests: no Goal seeded means no pennant on the debut; a Goal seeded onto the debut means the pennant is present.

### Separate regression, not this thread's and not touched

`RunViewFrameTest:92` expects one `role="radiogroup"` in the scrolling log region and finds two, because `5820e77` added a "Race entry mode" fieldset at `race-panel.blade.php:29`. `guided-step.blade.php:117` owns the other. Left alone deliberately — it is Slice 11's surface and Slice 11's regression.

### One measurement trap worth not re-hitting

A verification script in this thread reported "year 2 = 0, year 3 = 0" and the numbers were wrong, not the data: Laravel's `Builder::where()` **mutates in place**, so reusing one builder across several counts scopes every later query by the earlier `where`. Build a fresh query per assertion. Same class of trap as a chained `git grep A && git grep B` short-circuiting on the first no-match.

### Noted, not fixed

`SourceFetcher` writes snapshots with a `.html` extension even for JSON bodies — visible in the stored `snapshot_path` for all 410 rows. Pre-existing, inherited by the new source, out of scope here.

---

## slice-4-preflight-2026-10-01.md

## Slice 4 pre-flight — historical run import

Date: 2026-10-01. Read-only pass; no code written. Each answer is measured against the tree, not assumed
from the brief.

### A. `ApiV1ValidationEnvelopeTest` — resolved to **option 2: no API write endpoint**

The pinned assertion (`tests/Feature/ApiV1ValidationEnvelopeTest.php:69`) is not a comment, it is a filter
over the live route table:

```php
it('leaves the 422 branch unreachable, because the api has no write', function (): void {
    // …
    // Every route is a read. The moment one is not, VALIDATION_ERROR becomes reachable
    // through a FormRequest and this test is wrong - which is the point of asserting it.
    expect($routes)->not->toBeEmpty()
        ->and($routes->filter(fn ($route): bool => ! in_array($route->methods()[0], ['GET', 'HEAD', 'OPTIONS'], true)))
        ->toBeEmpty();
});
```

Two things settled the choice, and only one of them is this test:

- **`PRD.md` FR-E-1 scopes the API to reads**: "Versioned `/api/v1` **read** endpoints for catalog and runs".
  US-9 says "I **query** a local JSON API". There is no write requirement anywhere in §4.
- **`AGENTS.md`**: "Every new table, column, or class must cite a PRD requirement (FR-x / US-x). No citation,
  no merge." `POST /api/v1/training-runs/import` has no citation. Creating it would mean amending FR-E-1
  from "read" to something wider, which is an owner scope change, not a slice implementation detail.

So the test's premise stands and its comment gains one line saying the write surface was considered by
Slice 4 and declined on the PRD citation, not overlooked. Option 3 was checked and does not exist: nothing
about a file upload makes a route "out-of-band"; it is still an `api/` route and the filter still sees it.

**Consequence the import must honour:** it is a web form under `routes/web.php`, and every Trainer-owned write
in this app already lives there (`syncSkills`, `storeRace`, `storePurchase`, `storeTurn`, `syncDeck`).

### B. Field-by-field mapping — every sheet field has a home; one has no *format* column

Source of the inventory: `docs/deprecated/REVIEW-2026-09-30.md:308` — "Each sheet carries: stat values with
grade letters, skills with SP costs and acquired/skipped marks, mood tier, energy value, goal, and a
conditions row." Column lists read from the migrated schema.

| Sheet field                           | Lands in                                                                                       | Status                                                                                                                                                                                                                                                                                  |
| ------------------------------------- | ---------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| five stats                            | `turn_entries.speed/stamina/power/guts/wit`                                                    | holds; validated per scenario ceiling (Slice 1 / ADR-0015)                                                                                                                                                                                                                              |
| **stat grade letters**                | nowhere, **by design**                                                                         | Derived. `config('scenarios.php')` `grade_banding` is `'provisional' => true` with its step printed beside the badge, because no source defines a client stat grade. Importing a legacy letter would store this tool's own provisional scale as if it were the Trainer's observation.   |
| SP                                    | `turn_entries.sp`                                                                              | holds; non-negative, uncapped (ADR-0015)                                                                                                                                                                                                                                                |
| conditions row                        | `turn_entries.condition`                                                                       | holds, nullable, 255 chars                                                                                                                                                                                                                                                              |
| mood tier                             | `turn_entries.mood` → `MoodTier`                                                               | holds, **with a casing note**: the enum's backing values are the client strings uppercase (`GREAT/GOOD/NORMAL/BAD/AWFUL`) and the sheets write them lowercase. Import maps case-insensitively and leaves an unknown word null rather than failing the row.                              |
| energy                                | `turn_entries.energy`                                                                          | holds, 0–100 (ADR-0001)                                                                                                                                                                                                                                                                 |
| fans                                  | `turn_entries.fans`                                                                            | holds, non-negative                                                                                                                                                                                                                                                                     |
| skills + SP cost + acquired/skipped   | `run_skills.status` + `turn_acquired`; SP cost is on `skills`                                  | **has a home but is out of scope for the import** — see below                                                                                                                                                                                                                           |
| goal                                  | `race_entries.scenario_slot_id` / `objective_index`, `training_runs.current_objective_index`   | holds                                                                                                                                                                                                                                                                                   |
| which costume card                    | `training_runs.character_card_id`                                                              | holds                                                                                                                                                                                                                                                                                   |
| deck                                  | `deck_slots` (Slice 2 / ADR-0014)                                                              | holds; not on the sheets                                                                                                                                                                                                                                                                |

**Skills: named out of scope, not dropped silently.** Resolution needs a name→id lookup, and unresolved names
are exactly what `PRD.md` US-5's review queue exists to adjudicate. Importing by best-match would assign a
`skill_id` the source never stated, which fails the provenance floor. So the import writes turns and the run
header, and the preview screen says in words that skills are not imported and stay editable per-turn on the
run page.

**The format is the export's own header**, because a round trip is the test the brief asks for:
`turn,speed,stamina,power,guts,wit,sp,condition,energy,mood,fans` (`TrainingRunController::export()`). One run
per file; run identity comes from the form, since a turn CSV carries no trainee.

**Two candidate formats, and why CSV.** JSON is what `TrainingRunResource` already emits and would round-trip
skills and deck for free, so it is the lazier parser. It is rejected because the stated source is **seventeen
paper sheets** — a Trainer transcribing those types into a spreadsheet, not authors JSON. CSV serves the real
use; JSON stays the trivially-addible second shape.

### C. `imported_at` / `import_source` — no collision, so they are the first migration

`git grep -n "imported_at\|import_source" -- app database config` → **no matches**. The names are free.

Not to be confused with two existing pairs, which is the reason this check was worth running: `is_manual`
(a hand correction the engine must not overwrite, FR-B-4) and `source_url`/`fetched_at` (a fetched reference
row's provenance). An imported run is neither: it is Trainer-authored data that arrived by file rather than
by keystroke, so it needs its own pair on `training_runs`.

### Informational

- **No existing bulk-run-creation path to reuse.** `TrainingRunController::store()` creates one run from one
  validated request inside a transaction; `app/Actions/Store*` are all fetch-pipeline ingests of reference
  data, per the naming note in `ADR-0014` — `Store*` means "persist ingested catalogue rows", not "handle a
  Trainer form". The import action therefore follows `store()`'s transaction shape and is named
  `ImportHistoricalRun`, not `StoreRun`.
- **`scenario` normalisation is a live requirement, not a niceness.** Slice 2's browser pass found that a run
  whose `scenario` column is the empty string 500s the run page until `filled()` was introduced
  (`TrainingRun::hasScenario()`). An import path takes external strings, so it must map `''` to `null` before
  writing rather than trusting the FormRequest normaliser that the web form route runs.

### What Slice 4 therefore builds

ADR-0017 · one migration adding `imported_at`/`import_source` to `training_runs` · `ImportHistoricalRun`
action · a web import page with preview-before-commit · a one-line "imported" indicator on the run screen ·
tests that export a run and import it back · a browser pass on a scratch database. **No API endpoint.**

### Forward correction, appended when Slice 4 was built (2026-10-01)

Everything above stands as written at pre-flight time, except one line in §B, which the build overruled on
evidence. Recorded here rather than edited in place, because the file is committed at `cff8f46` and its
reasoning is the point of a pre-flight.

**§B, mood row: "an unknown word stays null rather than failing the row" is not what was built.**
`ImportHistoricalRunRequest::parse()` upper-cases the mood cell and leaves an unrecognised word untouched,
so `Rule::enum(MoodTier::class)` rejects it with its own message and the Trainer sees which row said it. The
preflight's version would have stored a blank mood for a sheet that stated one — a false statement about the
run, indistinguishable from a turn that recorded no mood. A rejected file is recoverable by fixing the word;
a silently nulled observation is not. `ADR-0017` carries the decision and this reason.

**One addition the pre-flight did not anticipate.** `str_getcsv()` is called with its `$escape` argument
explicit on PHP 8.5, where relying on the default is deprecated. This is not cosmetic here: an empty escape
is the correct reading of `export()`, which emits bare commas and no backslash quoting, so passing it keeps
the parser symmetrical with the emitter instead of merely silencing a notice.

**One finding the build surfaced that is not Slice 4's to fix.** A run with no scenario renders its stat
bands at 1,400 while its own form enforces 1,200 — the display path resolves `null` to the baseline scenario
and inherits URA Finale's bonus, while `ScenarioCaps::forRun()` deliberately refuses that bonus. That is the
disagreement `ADR-0015` exists to prevent, alive on the no-scenario branch, and the import reaches it on its
main path because paper sheets rarely recorded a scenario. Measured and filed as **KI-47**, escalated to the
human owner; not corrected inside this slice, since it changes a rendered ceiling on another slice's surface.

---

## slice-5-phase-a-best-for-2026-10-01.md

## Slice 5 Phase A — does a sourced `best_for` for skills exist? **No. Verdict (c): do not ship.**

Date: 2026-10-01. Read-only research. No migration, no column, no backfill, no UI, no scratch database.
Phase B is **not** authorised by this document.

### Verdict

**(c) Do not ship.** Two independent grounds, either of which is sufficient:

1. **No source carries the field.** Not GameTora's export, not Game8, not the in-repo scenario docs.
2. **No requirement cites it.** `PRD.md` contains zero occurrences of `best_for`, `best for` or `recommend`.
   Under `AGENTS.md` ("Every new table, column, or class must cite a PRD requirement. No citation, no
   merge."), the column cannot merge regardless of whether a source appeared. This is the same ground on
   which Slice 4 declined the API write, and it is the stronger of the two because it does not depend on
   the state of third-party websites.

The coverage bar in the dispatch — ">80% Global coverage" — is not close to met by any candidate: the best
number available from any source is **19.7%**, and that candidate is not a recommendation at all.

### 1. Schema check

`git grep -nE "best_for|recommended_usage" -- app database config resources routes tests docs` → **0 files**.
The names are free, so there is no existing column with a conflicting meaning to reconcile; an ADR would not
have had to note a collision.

The `skills` surface as it stands (`2026_09_29_021157_add_reference_fields_to_skills_table.php`): `export_id`,
`rarity`, `release_status`, `name_is_client`, and the provenance quartet `source_url` / `snapshot_path` /
`fetched_at` / `source_timezone` plus `is_manual`. Nothing in that set is a usage, role, target or priority
field.

**Where the term came from.** It exists once in tracked content, in an unrelated sense:
`docs/scenarios/02-unity-cup.md:45` — "Best for Sprint/Mile", about a **race distance**, not a skill. The
vocabulary is the slice's, not the project's.

### 2. GameTora key scan

Scanned twice, because the two artifacts answer different questions and only one of them is attributable.

- **Committed:** `tests/Fixtures/gametora-skills.sample.json` — 12 rows, 8 keys
  (`id`, `enname`, `jpname`, `rarity`, `condition_groups`, `name_en`, `cost`, `unreleased`). Per ADR-0011 the
  fixture is *cut from the live document* rather than hand-written, so its key names are the source's.
- **Untracked:** `database/seeders/data/skills.609afe88.json` — 1,910 rows, **30 keys**, 2,502,242 bytes.
  This body is **not on any ref**, and its `seed_file` declaration sits inside a peer's uncommitted
  `config/uma.php` diff, so the counts below are not attributable to a commit. `git show HEAD:config/uma.php`
  contains no `seed_file` key at all.

All 30 keys: `activation`, `condition_groups`, `desc_ko`, `desc_tw`, `endesc`, `enname`, `iconid`, `id`,
`jpdesc`, `jpname`, `rarity`, `tid`, `type`, `name_ko`, `name_tw`, `char`, `loc`, `unreleased`, `desc_en`,
`name_en`, `cost`, `versions`, `evo_cond`, `pre_evo`, `sup_e`, `sup_hint`, `evo`, `gene_version`, `char_e`,
`sce_e`.

**None is a usage, role, target, priority, tag or rating field.** The candidate filter was self-checked
before being trusted — 6/6 of `best_for`, `recommended_usage`, `skill_role`, `target_stats`, `notes`,
`priority` fire on the filter, and no real document key does. A zero from a filter that cannot fire is not
evidence; this one can. Probe: `.scratch-uma/cited/skill-key-values.php` (replayable).

The keys that could have been a `best_for` under another name are not one, read by value:

| Key                                           | Present                 | What it actually holds                                                                                                                |
| --------------------------------------------- | ----------------------- | ------------------------------------------------------------------------------------------------------------------------------------- |
| `char`                                        | 1490 (78.0%)            | The character ids whose **unique** skill this is — ownership, e.g. `It's Going to Be Me → [100302]`. Availability, not suitability.   |
| `sup_hint`                                    | 379                     | Support-card ids that can hint the skill — a source list.                                                                             |
| `type`                                        | 1910                    | Engine type codes (`nac`, `l_2`, `f_s`, `f_c`, `str`).                                                                                |
| `condition_groups`                            | 1910                    | Activation DSL: `{"condition":"is_last_straight==1","effects":[{"type":27,"value":4500}],…}`.                                         |
| `sce_e`                                       | 102                     | Scenario effect codes.                                                                                                                |
| `evo_cond` / `pre_evo` / `versions` / `evo`   | 672 / 672 / 821 / 346   | Upgrade chains.                                                                                                                       |
| `loc` / `unreleased`                          | 1308 / 1287             | Localisation payload; which locales lack the skill.                                                                                   |

#### The one signal that is a client fact, and its coverage

`docs/UMAMUSUME_REFERENCE.md:242` states the mechanic: a skill whose condition contains `running_style==N`
**cannot fire for any other style**, so the label rewrites the usable skill list rather than a hidden curve.
That is genuine "who is this for" information, published by the client, and it is the only such signal found.

Measured over the untracked body (1,910 rows):

```text
Global English subset (no 'en' in unreleased)  623  32.6%
carry any condition string                   1,910  100.0%
gated by running_style==                       646  33.8%   -> of the Global subset: 123, 19.7%
mention surface==                                0   0.0%
mention distance                               997  52.2%
  running_style==1  133 rows / ==2  320 / ==3  235 / ==4  141
```

The Global subset of 623 **matches ADR-0011's own committed figure** ("on the 623 …"), which corroborates
the reading of `unreleased` rather than leaving it as my invention. The app's predicate is
`Skill::scopeAvailableOnGlobal()` — `release_status = GlobalReleased`, and 535 of those 623 are client-named.

**19.7% is the ceiling of what could be sourced, and it is not a recommendation.** A style gate says when a
skill may fire; it does not say the skill is good. Presenting it as `best_for` would relabel an eligibility
fact as a judgement — the exact inversion this register has already caught twice, in the tier-label hold and
in the provisional `grade_banding`.

### 3. Game8

Both relevant surfaces are **Global-current**, dated **September 28, 2026**:

- **Skills tier list** (`game8.co/.../archives/536805`): columns are exactly **`Tier` / `Skill` / `Points`**.
  **No target column.** Fetched content notes that specific use cases ("dirt tracks", "late-race") appear
  *inline in prose descriptions*, not as a field. Roughly 60+ skills listed against 623 Global rows — about
  10%, and it is a ranking rather than a mapping.
- **Per-character build guide** (`archives/536303`, Daiwa Scarlet): a "### Recommended Skills" section,
  categorised as Velocity / Acceleration / Recovery / Others, presented as **bare linked skill names with no
  per-skill "best for" annotation**. The relation is character → skills, one page per trainee, so inverting it
  into skill → characters would require crawling every guide and would still be **editorial opinion**, not a
  client fact.

The precedent that settles how to treat this: the Slice 2 ruling held card tier labels because Game8's list is
"dated and scored at Max Limit Break". The same class of objection applies here — a third-party score, on
their subset, restated as this tool's fact.

### 4. Third source: the two named scenario docs

Checked, and **neither carries skill-role framing**. What they do say about skills:

- `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "Trackblazer (uma.guide)" — priority language exists but points at **shop items and deck
  composition**, not at skills: "Both Must Buy; +1 has priority" (mood items, :156), "Low priority filler
  only" (stats item, :155), "Power cards that carry good skills" (:19, about *support cards*).
- `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "Trackblazer (GameTora)" — skills appear only as **hint sources**: "Winning (1st place)
  vs. the rival grants one random skill hint, tied to either the race's distance or the running style you
  used" (:127), and unique-skill level-up conditions (:150-155).

The concept sweep across `docs/scenarios/` and `docs/UMAMUSUME_REFERENCE.md` returned three hits, and the two
that are not the scenario docs above are:

- `UMAMUSUME_REFERENCE.md:146` — the closest thing to a real skill-priority concept anywhere: the
  「スキルセット」 feature sorts desired skills into **three priority groups** (超優先 / 優先 / 通常). It is
  **`[JP]`** official news 2026.09.11, it is **player-authored** rather than published per skill, and the same
  line records that **no `[Global]` notice for an equivalent feature** appears in the Global news index
  checked 2026-09-27. So the game's own notion of skill priority exists, is a personal list, and is not on
  Global.
- `docs/scenarios/09-global-race-calendar.md:47` — a `G1` averseness skill quote with Game8's per-race tier;
  a race-tier citation, not a skill-role taxonomy.

### 5. What would be worth building instead, if anything is

Recorded as an option, **not** as a recommendation and not as authorisation:

The client does state eligibility for 123 of 623 Global skills. If that number is ever worth surfacing, the
correct shape is a **derived badge on the existing skill screen**, computed from `condition_groups` at read
time, saying which running styles the skill can fire in — because that is a fact the source states, it needs
no new column, and it cannot drift from the data it is derived from. It would need the style-number-to-label
mapping resolved from client strings first: `factors.json` ids 21-24 give **Front Runner / Pace Chaser /
Late Surger / End Closer**, and the labels in wider circulation ("Runner / Leader / Betweener / Chaser")
are not the shipped strings — "Betweener" has zero occurrences in client data. No count in this document is
labelled for that reason.

A `best_for` column would instead store, per skill, a judgement no source makes, at 19.7% coverage at best,
against a requirement `PRD.md` does not contain.

### 6. Fence confirmation

Honoured in full. No `config/uma.php` write, no `phpunit.xml` write, no migration, no column, no backfill,
no UI, no `migrate`, no scratch database. Nothing was committed to the schema and no `.sqlite` file was
touched by this phase — the shared development database was neither read nor written.

Reads performed: `app/`, `config/`, `database/migrations/`, `docs/scenarios/`, `docs/UMAMUSUME_REFERENCE.md`,
`docs/adr/0011`, `tests/Fixtures/`, the untracked skills JSON (read only, provenance stated above), and two
public Game8 pages for the sole purpose of establishing whether a column exists on them.

Two provenance limits carried forward rather than hidden:

1. The 1,910-row coverage counts come from an **untracked** body whose `seed_file` line is in a peer's
   uncommitted diff, so they are not attributable to a ref. The Global subset size (623) is corroborated by
   ADR-0011's committed text, which is what makes the number usable despite that.
2. The Game8 column reading is a **snapshot of a live page** on 2026-10-01. If Game8 restructures, re-check
   before relying on "no target column".

Probes, replayable: `.scratch-uma/cited/skill-key-scan.php`, `.scratch-uma/cited/skill-key-values.php` (includes the
filter self-check), `.scratch-uma/cited/skill-eligibility-coverage.php`. All three live under `.scratch-uma/`,
which `.gitignore:88` excludes.

Sources:

- [Best Skills Tier List | Umamusume — Game8](https://game8.co/games/Umamusume-Pretty-Derby/archives/536805)
- [Daiwa Scarlet (Peak Blue) Build Guide — Game8](https://game8.co/games/Umamusume-Pretty-Derby/archives/536303)
- [Game8 all-races table, cited in `docs/scenarios/09-global-race-calendar.md:47`](https://game8.co/games/Umamusume-Pretty-Derby/archives/536131)

---

## slice-6-currency-2026-10-01.md

## Slice 6 — documentation currency pass: PRD and its cross-references

Date: 2026-10-01. Read the PRD, fixed exactly two lines elsewhere (KI-48), filed everything else.
No `PRD.md` content change was made by this pass. No migration, no schema, no code, no scratch database.
`config/uma.php` and `phpunit.xml` were not read as inputs and not written.

### 1. Method note

**Scanned.** `PRD.md` (191 lines) in full: every `§` reference, every `FR-*`/`US-*` token, every counted
claim, every backticked token. Each outbound path was resolved against the tree; each external `§` target was
opened and read. `AGENTS.md` and `CONSTRAINTS.md` were read for the two rules recent decisions cited.
`docs/research-scratch/GOVERNANCE.md`, `docs/UMAMUSUME_REFERENCE.md`, `docs/adr/0003`, `docs/adr/0008`,
`docs/adr/0015` and the Slice 5 Phase A record were opened only to resolve a citation that pointed at them.

**Tooling.** `git grep` with a canary per pattern, plus three replayable probes under gitignored
`.scratch-uma/`: `prd-outbound-citations.php` (path resolution), and for the requirement-ID map the loop
recorded in §2. No test was run, because nothing executable changed; the gate results are in §4.

**Excluded, and why.** Product decisions: where the PRD and shipped behaviour disagree, this pass records the
disagreement and does not resolve it. `ARCHITECTURE.md` and `ARCHITECTURE-ESSENTIALS.md` were touched **only**
for KI-48's two lines — a listing gap found in them is reported in §5, not fixed. `docs/deprecated/**` was
not opened for editing, only `docs/deprecated/REVIEW-2026-09-30.md` §7 was used as the shape precedent for
the citation scan.

**Two discipline notes, because both changed an answer mid-pass.**

1. **A first regex silently matched zero `FR-*` ids.** The pattern was `FR-[A-Z]?[0-9]+`, which cannot match
   `FR-A-5` — there is a hyphen between the letter and the digit. The zero was a blind filter, not an
   absence. Re-run with `FR-[A-Z](-[0-9]+)?` plus a canary (`does FR-C-1 appear in PRD.md?`) before any
   number in this document is trusted. The canary is what surfaced §2.1's central finding.
2. **A first path classifier reported seven broken citations; four were not citations at all.** `/api/v1` is a
   URL prefix, `Asia/Tokyo` a timezone value, `maatwebsite/excel` a composer package, `.json` prose. A scanner
   that treats "backticked and contains a slash" as a file reference manufactures broken links, so the script
   was rewritten to enumerate evidence and leave classification to the reader. N was 22; that was the right
   trade.

### 2. PRD internal consistency findings

Each line is `file:line` plus the evidence, per instruction. **Filed, not fixed.**

- **P-1 — the PRD does not use the `FR-` prefix that every citation to it uses.**
  `PRD.md:41,118,126,142,147` define sub-requirements as `- A-1:`, `- B-4:`, `- C-1:`, `- D-2:`, `- E-1:`
  under `### FR-A:` … `### FR-E:` headings. `git grep -c "FR-C-1" -- PRD.md` returns **0**. Meanwhile
  **26 distinct `FR-X-N` ids** are cited outside the PRD (~250 occurrences; `FR-B-4` alone 43). Mapping each
  through the `FR-` strip rule: **25 resolve to a real `- X-N:` item, 1 does not** (see P-2). So the drift is
  systematic, resolvable by a human, and ungreppable inside the PRD. `US-*` ids are literal (`| US-1 |` at
  `PRD.md:26`), so the two families are inconsistent with each other. Origin is `AGENTS.md:25` — see §4.
  *Severity: notation drift, not a broken promise. Fix is one line in AGENTS.md or a prefix added to the PRD
  list markers; either way it is the next slice's, and it must be one of the two, not both.*

- **P-2 — orphan requirement: `FR-C-7` is cited and was never created.**
  `docs/adr/0003-consolidated-phase1-schema-expansion.md:104`: "add FR-C-6 for Energy and Fans, **FR-C-7 for
  event and race logging**, and amend §6.11". `PRD.md` FR-C ends at `C-6`. The tables that requirement would
  cover **are shipped** (`turn_events`, `scenario_races`, `race_entries`, all migrated on 2026-09-27), so this
  is not an unbuilt feature — it is a written requirement that never landed in the document while its schema
  did.

- **P-3 — `FR-C-6` means two different things.** The same ADR line proposes `FR-C-6` **for Energy and Fans**.
  `PRD.md:132` uses `C-6` for **`DeckSlot`**, added later under `ADR-0014` (owner authorization 2026-09-30).
  The slot was claimed by a different requirement after the plan that reserved it. A reader following
  `FR-C-6` out of ADR-0003 reaches the deck.

- **P-4 — three shipped columns have no PRD requirement.** `turn_entries` carries `energy`, `mood`, `fans`
  (verified in the creating migration), the guided form collects them, and `export()` emits them. `PRD.md:128`
  (C-2) lists "five stat integers, SP integer, optional condition string" and stops there. Across the whole
  PRD, the words energy / fans / mood appear in **one place only: `PRD.md:184`, my own OQ-5 aside**. This is
  the concrete cost of P-2 and P-3: the requirement that would have cited them was planned as `FR-C-6`, never
  written, and its number was then reused. Under `AGENTS.md:25` ("every new … column must cite a PRD
  requirement"), these three are uncited columns on a shipped table. *This is a PRD-vs-shipped disagreement,
  so per scope it is filed and not fixed; it is also the single most consequential item in this pass.*

- **P-5 — `ADR-0015`'s supersession reached the PRD but not the architecture docs.** `PRD.md:28` and `:128`
  both carry the supersession note; `ARCHITECTURE-ESSENTIALS.md:36` and `ARCHITECTURE.md:158` did not. That is
  **KI-48**, and it is the one finding this slice was told to fix — done in §3.

- **P-6 — one citation inside the PRD points at an untracked file, and it is mine.** `PRD.md:184` (OQ-5) says
  the style labels come from "`factors.json` ids 21–24". `git ls-files | grep -i factors` returns **nothing**;
  the file exists only under ignored scratch dirs. The *claim* survives independently on tracked content —
  `database/migrations` defines exactly four running-style aptitude columns, `aptitude_front_runner`,
  `aptitude_pace_chaser`, `aptitude_late_surger`, `aptitude_end_closer` — but the *pointer* is not durable.
  Filed for the next PRD pass: retarget the citation to the aptitude columns. Not edited here, because the
  fence says the PRD is read, not rewritten.

- **P-7: the four running-style counts at `UMAMUSUME_REFERENCE.md:231-234` do not state their denominator.**
  The column "Skills gated to it" reads 107, 220, 166 and 115. Measured over the whole export body
  `database/seeders/data/skills.609afe88.json`, which holds 1,910 records, counting skills whose condition set
  pins exactly one `running_style` value gives 107, 220, 166, 115. Those four numbers reproduce, so the table
  is not wrong. Measured over the 623 records that carry no `unreleased` key, the same definition gives 26, 35,
  33, 27. The caption at `:236` names the predicate, "skills whose condition set pins exactly one
  `running_style` value", and cites the export, but never names the set the count runs over, so a reader cannot
  tell which of the two it is. Every consumer downstream shows the smaller list: `app/Enums/ReleaseStatus.php`
  renders Global states only, and `docs/design-research/skill-facts-2026-10-01.md` covers exactly the 623. So
  the reference's numbers would sit beside a list a third their size with nothing on the page saying so. Fix is
  one clause stating the denominator. Do not change the figures; they are correct for what they count. This is
  a Docs Writer item and not a KI, because no shipped behaviour reads those four cells. Filed here beside P-6
  rather than in the register, since it is the same class of citation-hygiene defect and P-6 already anchors the
  pair. Measurement: parse `database/seeders/data/skills.609afe88.json`, collect the distinct
  `running_style==N` values from `condition_groups[].condition` and `.precondition` per record, and count
  records whose set has exactly one member, once over all 1,910 records and once over the 623 with no
  `unreleased` key. The script that ran it is `skill-facts-2026-10-01.md`'s generator, and that document
  reports both figures: the per-label Global counts in its coverage table and the whole-export counts in the
  reconciliation paragraph beneath it.

  **Closed as a verification question, 2026-10-01.** Re-measured independently against the same body: 1,910 records
  give 107, 220, 166 and 115, and the 623 records with no `unreleased` key give 26, 35, 33 and 27. All four style
  values were observed in the data, which is the canary that makes both counts real rather than a pattern that
  cannot fire. The finding is confirmed. What remains is the fix itself, one clause naming the denominator, and
  that is Docs Writer work. It is not changed by this closure.

**Checked and clean — recording these because a currency pass that names nothing checked is unauditable.**

- **All 10 `§` references resolve.** Internal: `§3`, `§5`, `§6` (`PRD.md:35–37`), `§6.9` (`:37,90`), `§6.11`
  (`:35,75,110`) — `§6` is a numbered 1–13 list, so `.9` and `.11` resolve by ordinal to items 9 and 11.
  External: `docs/research-scratch/GOVERNANCE.md` §4.1 exists at its line 67; Phase A doc §5 exists at its line 136;
  `docs/UMAMUSUME_REFERENCE.md` §1.2.2 spans 225–249 and does carry the eligibility mechanic at 242.
- **No counted claim is stale.** "Ten aptitude letters" (`OQ-4`) is exactly the 10 `aptitude_*` columns in the
  migrations. "Slot position one to six" (`C-6`) matches `DeckSlot::POSITIONS`. "CSV/JSON only" matches
  `export()`'s two formats. `E-1` states no endpoint count, so there is nothing to rot.
- **Supersession notes are present where the ADRs landed.** §6 item 3 carries `ADR-0010` narrowing; item 9
  carries `ADR-0014`'s half-lift and the still-cut collection; `C-2` carries `ADR-0015`; `C-6` carries
  `ADR-0014`; `D-1`/`D-3` carry `ADR-0011`. §6 item 11 is correctly untouched by `ADR-0016`, which explicitly
  declines to amend it.
- **No requirement ID appears twice inside the PRD with two meanings.** The duplication the scan found
  (`FR-A-5` twice at `:183`, `US-7` at `:32` and `:36`, `US-1`/`US-3` in §3 and §8) is same-id-same-meaning
  reuse. The collision is *across* documents: P-3.

### 3. PRD outbound citation resolution

22 backticked candidates, classified by a reader after the scanner printed evidence.

| Class                     | Count   | Items                                                                                                                                                                                                                                                                             |
| ------------------------- | ------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Resolves from repo root   | 10      | `AGENTS.md:59`, `tests/Feature/CatalogDetailPageTest.php:82`, `docs/adr/0012…:87`, `CONSTRAINTS.md:143,166`, `DESIGN.md:180`, `config/uma.php:181`, `KNOWN-ISSUES.md:184`, `docs/UMAMUSUME_REFERENCE.md:184`, `docs/design-research/slice-5-phase-a-best-for-2026-10-01.md:184`   |
| Loose but findable        | 3       | `components/layout.blade.php:36` → `resources/views/components/…`; `2026_09_29_182820_create_umamusume_profiles_table.php:84` → `database/migrations/…`; `factors.json:184` → **not tracked** (see P-6)                                                                           |
| Not citations at all      | 8       | `GET /umamusume…:26,31`, `.json:31`, `/api/v1:34,148`, `Asia/Tokyo:124`, `maatwebsite/excel:168`, `Tier / Skill / Points:184`                                                                                                                                                     |

**Broken: 0 of 22. Stale: 1 (`factors.json`, P-6). Resolvable: 21.** Two of the three loose paths omit a
directory prefix, which is style rather than rot — a reader finds them — but neither is greppable from the
PRD as written.

**Line-anchored citations inside the PRD: none.** `PRD.md` cites documents and sections, never `file:line`,
so the past-EOF class of rot cannot occur here. That is worth knowing: the PRD's citation surface is
section-level, and section-level anchors survive refactors that would break a line number.

### 4. KI-48 fix

Two edits, both forward-append errata following `ADR-0008`'s dated-erratum pattern: the historical sentence
stands verbatim and the correction is appended with its date. Both cite `ADR-0015` and `8bda7db`
(verified: `git log -1 8bda7db` → "feat(bounds): each stat is capped by its own scenario, not a flat 1200",
2026-09-30 22:16:14 +0800 — and `git log --diff-filter=A` on the ADR file returns the same sha, so the brief's
attribution is correct).

**`ARCHITECTURE-ESSENTIALS.md:36`**

Before, ending the line:

> `… uniq(run, turn); stats validated 0..1200, turn >= 1 [rev 0.2 — repo #4]`

After, same line with the erratum appended (nothing above it altered):

> `… uniq(run, turn); stats validated 0..1200, turn >= 1 [rev 0.2 — repo #4]. **Bound corrected 2026-10-01 by a dated erratum; the`0..1200`above stands as the state as written.** ADR-0015 (owner decision 5, 2026-09-30, landed in 8bda7db) replaced the flat bound with the run's **own per-stat scenario ceiling** — base_cap (1200) plus that scenario's cap_bonus for that stat, clamped to the engine hard_cap (2000) — read through App\Services\ScenarioCaps … So a stat is accepted to 1400 on URA Finale, to 1800 on Unity Cup Wit, to 1900 on Trackblazer Stamina and to 1600 on Our Grand Concert Speed; **1200 is the ceiling only where the run names no scenario**, which is the one case this sentence still states correctly … PRD.md carried this supersession from the start (:28, :128); the two architecture documents did not, and this is one of the two carriers recorded as **KI-48**.`

**`ARCHITECTURE.md:158`** — the listing uses `--` comment lines, so the erratum is five further `--` lines
under the untouched original:

Before:

> `-- stats validated 0..1200, turn >= 1 (StoreTurnEntryRequest) [rev 0.2 — repo #4]`

After: the same line verbatim, then `-- BOUND CORRECTED 2026-10-01, dated erratum; the line above stands as
the state as written.` and the `ADR-0015` / `8bda7db` / `ScenarioCaps` explanation with the same four ceilings,
the same no-scenario exception, and the KI-48 pointer.

Both per-stat numbers were read from `config/scenarios.php` `cap_bonus` and checked against
`ADR-0015`'s table, not typed from memory: URA 200 across all five; Unity Cup 100/100/100/100/600; Trackblazer
0/700/0/0/300; Grand Concert 400/100/100/300/100, over `base_cap` 1200, clamped at `hard_cap` 2000.

**Gate results for this change.** Documents only, no executable code, so no test was added. Run for
confirmation of no breakage: `php artisan test --compact tests/Feature/DocSchemaDriftTest.php` (that guard
reads `ARCHITECTURE.md`, `ARCHITECTURE-ESSENTIALS.md`, `docs/adr/0008…` and `CONSTRAINTS.md`, i.e. exactly the
files edited here) — green. Lore grep over the added lines: no banned-pattern hits.

### 5. AGENTS.md and CONSTRAINTS.md — informational results

**`AGENTS.md:25`** — "Every new table, column, or class must cite a PRD requirement **(FR-x / US-x)**. No
citation, no merge." The rule as written is still how it is being applied, and both recent refusals used it
correctly: Slice 4 declined `POST /api/v1/training-runs/import` because `E-1` scopes the API to reads, and
Phase A declined `best_for` because no requirement mentions it. The application is sound; the **notation is the
origin of P-1**. The rule tells an agent to write `FR-C-1`, and `FR-C-1` is a string that appears nowhere in
the PRD it is citing — so every correct citation produced by following the rule is a string that cannot be
grepped back to its target. Suggested wording, **not applied**: after "(FR-x / US-x)", note that the PRD's own
markers are `- C-1:` under `### FR-C:`, so a citation `FR-C-1` resolves by stripping the prefix. That is a
one-clause edit to a governance rule and belongs to the owner, not to this pass.

One more consequence worth naming: the rule says *column*, and P-4 is three shipped columns with no
requirement. The rule was enforced on new work in this period while older columns went uncited, so the gate is
prospective rather than retrospective. If it is meant to reach back, that is a decision; nothing here assumes
it.

**`CONSTRAINTS.md:9` (C-1)** — "All Pest tests pass; every behavior change ships a test", command
`php artisan test --compact`. **No finding.** C-1's command is exactly what has been run for every gate in
this period, and the "ships a test" clause is what put 11 tests behind the import. `C-2` (`vendor/bin/phpstan
analyse --no-progress`) and `C-3` (`pint --dirty` then `pint --test`) likewise match the commands actually
used. One adjacent caveat, already on the record and not a C-1 wording defect: whether the suite touches a
file-based database is decided by an **uncommitted** `phpunit.xml` change, so C-1 is satisfiable today in a
way a fresh clone is not. That is the escalation row's problem, not the gate's text.

### 6. Not-slice-6 items surfaced

Named, not fixed. Each has a different remit.

- **N-1 — three uncited shipped columns (`turn_entries.energy`/`mood`/`fans`), and the `FR-C-6` collision and
  `FR-C-7` orphan behind them.** (P-2, P-3, P-4.) Remits to the **human owner and the Architect**: closing
  this means writing a PRD requirement, which is a product statement, not a documentation fix. Recommended as
  one item, because the three findings are one story — a planned requirement that never landed, whose number
  was then reused.
- **N-2 — the architecture listings omit `energy`, `mood`, `fans` as columns.** `ARCHITECTURE.md:154–156` and
  `ARCHITECTURE-ESSENTIALS.md:36` both list `turn_entries` fields without them, which is the same
  under-reporting as N-1 at the schema layer. **Deliberately not fixed**: the fence limits those files to
  KI-48's two lines, and this is not a consequence of `ADR-0015`. Remits to the Docs Writer with N-1.
- **N-3 — `factors.json` is cited from the PRD but is not tracked.** (P-6.) **Remits to the Docs Writer, not
  the Data Engineer — owner's ruling 2026-10-01, correcting this entry's first draft.** The fix is a citation
  change, not a data change. Two paths were weighed: commit the body under `database/seeders/data/` with a
  `seed_file` entry, which is the peer's in-flight pattern and would collide with `config/uma.php`'s current
  state; or retarget `PRD.md:184` to the tracked aptitude columns — `aptitude_front_runner`,
  `aptitude_pace_chaser`, `aptitude_late_surger`, `aptitude_end_closer` — which carry the same four styles on
  tracked content. **The second is smaller, needs no data decision, and is the one to take.**
- **N-4 — the `FR-` prefix notation drift.** (P-1.) Remits to whoever owns `AGENTS.md:25`. One clause fixes
  it; doing it in the PRD instead means re-prefixing ~30 list markers, which is a larger and worse change.
- **N-5 — the product question surfaced while filing OQ-5, preserved here so it is not lost with the
  withdrawn draft.** *Does the tool advise, or only record?* The OQ-6 draft was withdrawn as unasked-for
  scope; if the owner wants it stated, the wording is recoverable from `b7f105e`'s neighbourhood. Filed here
  so the question is not lost with the draft.

### 7. Fence confirmation

`config/uma.php` — untouched, still dirty with the peer's work; not read as an input to any conclusion except
where a `cap_bonus` value was verified, which is a read of a file this pass does not edit. `phpunit.xml` —
untouched. No migration, no schema, no column, no backfill, no scratch database, no `.sqlite` read or
written. **No `PRD.md` content change**: the PRD was read, and every finding that needs it is filed in §2 and
§6. `ARCHITECTURE.md` and `ARCHITECTURE-ESSENTIALS.md` changed only at KI-48's two lines, both as appended
errata with the historical sentence left verbatim. The three re-verification counts were not run; the config
gate holds.

Prose note for the record, since `antislop-copywriting` R-02 forbids em dashes outright while this repo's
documentation uses them throughout: house style was kept for consistency with the other ~40 documents in this
tree, and the one `--` that crept into the `ARCHITECTURE.md` erratum was removed because inside a block whose
comment marker *is* `--` it reads as a nested comment. Flagged rather than silently resolved.

---

## slice-7-prd-revision-draft-2026-10-01.md

## Slice 7 — PRD revision draft: the uncited shipped surface

**DRAFT. NOT APPROVED. NOTHING HERE IS APPLIED.** `PRD.md`, `AGENTS.md`, `CONSTRAINTS.md` and the ADRs are
untouched by this file; it exists so the owner can decide shape before anyone writes product text. Every
claim below is marked with the command that shows it, because that is the lesson of the pass that produced
this list.

### 0. Three of this draft's four inputs are already closed — stated first because it changes what is left

- **P-1 / N-4 (the `FR-x` notation drift): closed.** `AGENTS.md:25` now reads "The PRD writes its own markers
  as `- C-1:` under a `### FR-C:` heading, so a citation `FR-C-1` resolves by stripping the prefix and
  matching the bullet's number" — committed at `539de59`, "batch-1 item 1". No recommendation is made.
- **N-2 (the two architecture listings omitting `energy`/`mood`/`fans`): closed.** `46959ab`, "batch-1 item 2,
  N-2", added all three to both `turn_entries` listings — `ARCHITECTURE.md:157` and
  `ARCHITECTURE-ESSENTIALS.md:36`. This draft's first cut asserted N-2 was still open and cited those same two
  lines as lacking the columns; it was wrong, and the error was caught only by running the draft's own
  re-derive commands before submitting. **Everything in §3 below is therefore about a defect that no longer
  exists, and is retained only as the record of how it went stale.**
- **KI-47: discharged** at `04658f2` / `c8160ae`. Not listed as pending.

So the only substantive item left is **P-2 / P-3 / P-4** — the missing PRD requirement — plus **R-02**.

This is the fifth wrong dispatch premise of the session, and the first that went stale rather than being
mistaken: the work was correct when scoped and another session finished it mid-draft. In a shared worktree with
one identity and no announcement channel, "the owner queued this" is not evidence that it is still open.
Re-running the status commands in §6 costs seconds and is why they are written there rather than as prose.

One thing checked because a peer had edited the same lines: **KI-48's dated erratum survived `46959ab`**
(`grep -c 'Bound corrected 2026-10-01' ARCHITECTURE-ESSENTIALS.md` → 1), so the append-only convention held
across a concurrent edit rather than being overwritten by it.

### 1. The verified state

| Shipped object                           | Exists                                   | Cited in `PRD.md`                           | Evidence                                                                                         |
| ---------------------------------------- | ---------------------------------------- | ------------------------------------------- | ------------------------------------------------------------------------------------------------ |
| `turn_entries.energy`                    | yes, `unsignedSmallInteger` nullable     | **no**                                      | creating migration; one occurrence of the three words in `PRD.md` total, inside `OQ-5`'s aside   |
| `turn_entries.mood`                      | yes, `string(20)` nullable, `MoodTier`   | **no**                                      | same                                                                                             |
| `turn_entries.fans`                      | yes, `unsignedInteger` nullable          | **no**                                      | same                                                                                             |
| `turn_events` (table)                    | yes, `2026_09_27_093947`                 | **no — 0 occurrences**                      | `grep -c turn_events PRD.md` → 0                                                                 |
| `race_entries` (table)                   | yes, `2026_09_27_093950`                 | **no — 0 occurrences**                      | `grep -c race_entries PRD.md` → 0                                                                |
| `scenario_races`, `race_catalog_slots`   | yes                                      | once, inside FR-A, about reference fields   | §4 line 14 of the FR block                                                                       |
| `deck_slots`                             | yes                                      | yes — `C-6`, `ADR-0014`                     | —                                                                                                |
| `preferences`                            | yes                                      | yes — `US-11`                               | —                                                                                                |

Validated but uncited: `StoreTurnEntryRequest` enforces `between:0,100` on energy, `Rule::enum(MoodTier)` on
mood, `min:0` on fans. So the app has three rules and two tables that no requirement names, while
`AGENTS.md:25` requires a citation per column and per class.

**One counter-argument the draft must not skip, because it may make most of this a non-finding:** `US-10`
("I track race goals and predictions per run", P1, acceptance naming `scenario_slots` kinds including
`scripted_event`) arguably *is* the requirement for race and event logging, cited at story rather than
FR level. If the owner accepts that, `turn_events` and `race_entries` are already authorized and only the
three columns need a home. **This is a product judgement, not a measurement, and it is decision A below.**

### 2. Decision A — how to close the gap. Two shapes, both drafted

#### Shape 1 — write the missing requirement(s), and leave `FR-C-6` where it is

Add one bullet after `C-6` covering the turn-outcome fields, and (if the `US-10` reading is rejected) a second
covering event and race logging. The number `C-7` is free in the PRD: nothing named `- C-7:` exists, and the
only `FR-C-7` in the repo is `ADR-0003:104`'s plan, which was never created. Draft:

> - C-7 [added 2026-10-01, closing the gap `ADR-0003` §104 planned as FR-C-6 and never wrote]: `TurnEntry`
>   records three outcome fields beside the five stats — Energy (`0..100`, `ADR-0001`'s lift), mood
>   (`MoodTier`, the five client strings) and cumulative fans (non-negative, no ceiling: no source states
>   one). All three are Trainer-entered and none is derived. They are exported and rendered, so a run read
>   back two seasons later shows the state the turn ended in rather than only its stat deltas.
> - C-8: a run logs turn events and race entries. Optional per scenario: `ADR-0016` holds the readiness
>   panel and §6.11 still forbids simulating an outcome, so logging is in scope and prediction is not.

Shape 1's cost: it makes `FR-C-6` permanently mean "the deck" while `ADR-0003:104` says it meant "Energy and
Fans". That collision is then recorded, not removed — see Decision B.

#### Shape 2 — a superseding decision that closes it the other way

Add `ADR-0018` recording that `ADR-0003`'s planned `FR-C-6`/`FR-C-7` were never written, that `C-6` was reused
by `ADR-0014`, and that the shipped columns and tables are hereby cited by a new PRD bullet with `ADR-0003`
annotated. Cost: a second document to say what one PRD bullet says in Shape 1. Its benefit: `ADR-0003` is where
a reader meets the stale number, so that is where the correction is noticed.

**Recommendation, marked as a recommendation only:** Shape 1, because `PRD.md` already carries this exact kind
of note (`C-6 [ADR-0014; owner authorization 2026-09-30]`, `D-1 [amended 2026-09-29 by ADR-0011]`), so the
convention exists and no new document is needed.

#### Both shapes use the same correction form

Per `docs/adr/README.md` §1 and `ADR-0015`'s erratum: when the text touches `ADR-0003:104`, keep the line
verbatim and append a dated note that says which part still holds. Draft for `ADR-0003`:

> **Erratum 2026-10-01.** This line proposed `FR-C-6` for Energy and Fans and `FR-C-7` for event and race
> logging. Neither was ever added to `PRD.md`, and the `C-6` slot was taken by the deck on 2026-09-30 under
> `ADR-0014`. The intent stands and is now cited at `PRD.md` `C-7`/`C-8`; what did **not** survive is the
> numbering, which is why reading this line as a current identifier misleads.

### 3. ~~Decision B — N-2, the two listings~~ — CLOSED by `46959ab`, no decision remains

Kept as written-then-withdrawn rather than deleted, because the withdrawal is the finding: N-2 was real at the
moment Slice 6 filed it, and the remedy this draft would have proposed was the same
preserve-the-sentence-append-a-dated-note form used for KI-48. `46959ab` instead edited both listing lines
directly to add `energy`/`mood`/`fans`, which is correct for a *listing* — a schema digest has no claim whose
reasoning needs preserving, unlike a dated ADR consequence. The distinction worth carrying into Decision A:
**a listing may be edited in place; an assertion with reasoning gets an erratum.** That is why the same session
treated `ARCHITECTURE-ESSENTIALS.md:36` two ways within one week and neither was wrong.

### 4. Decision C — R-02's em-dash ban against the corpus. Owner's call, all three readings live

`antislop-copywriting` R-02 forbids the em dash in any text. Roughly 40 files under `docs/` use it as ordinary
prose rhythm, including `ADR-0015`, this repo's `KNOWN-ISSUES.md` and the ADR index created the same day.

- **(i) The rule is right and the corpus drifted.** Then remediation is a real project, and until it is done
  the rule is unenforced — the worst state, because a writer cannot tell which way to go.
- **(ii) The rule is scoped to shipped copy.** Then `docs/**` is out of scope and R-02's own text should say
  so in one clause. Cheapest to act on, and it matches how the rule is actually honoured: user-visible UI
  strings are dash-free while prose is not.
- **(iii) The rule targets dash *clusters*, not the character.** Consistent with the skill's own "What NOT to
  flag" section ("Em dashes alone… is evidence only inside a cluster"). Then the ban should be restated as
  clustering guidance and the corpus is already fine.

The draft takes no side. What it records is the cost of not choosing: **every agent writing a document here is
currently guessing, and the guess is invisible in the output** — which is how this arrived in seven files
before anyone counted. If (ii) or (iii) is chosen, one clause settles it; if (i), the choice deserves its own
dispatch rather than a sweep.

### 5. Deliberately not in this draft

`config/uma.php` (the peer's), the three re-verification counts (they run against the peer's commit, on a named
ref), `PRD.md` content, any migration or column, `OQ-5`/`OQ-6` (the advise-or-record question stays unfiled,
owner's), and the deprecated-PDF and Request-D items.

### 6. Re-derive before acting

```bash
## the column claim: expect exactly ONE hit, and it is OQ-5's own aside, not a requirement
grep -in 'energy\|mood\|fans' PRD.md

## the table claim: expect zero
grep -in 'turn_events\|race_entries' PRD.md

## confirms C-7 and C-8 are free numbers in FR-C
awk '/^### FR-C/,/^### FR-D/' PRD.md | grep -c '^- C-[78]:'   # expect 0

## confirms P-1 and N-2 are CLOSED (both expect a hit, i.e. the opposite of this draft's first run)
git show HEAD:AGENTS.md | grep -c 'stripping the prefix'
grep -n 'energy.*mood.*fans' ARCHITECTURE.md ARCHITECTURE-ESSENTIALS.md   # lines 157 and 36

## confirms this draft's own erratum survived the concurrent edit
grep -c 'Bound corrected 2026-10-01' ARCHITECTURE-ESSENTIALS.md           # expect 1
```

`-i` on the PRD greps is not decoration: the first cut of this draft dropped it, and a case-sensitive grep
would have reported zero for a document that says "Energy" somewhere. Same reason every phrase check in this
session is now run over joined lines — `DocSchemaDriftTest` documents the blind spot where a claim wraps across
two lines and a line-scoped grep silently finds nothing.

Commands rather than counts, because every number in this history that was typed instead of re-run turned out
to be wrong or vacuous, and this draft's own opening premise was one of them.

---

## SESSION-CONSOLIDATION-2026-09-30.md

## Session Consolidation and Verification — 2026-09-30

> **Citation pin.** Every line number and every live-state count (commits ahead or behind, files untracked, migrations per worktree, files matching a search) in section 1 is a snapshot measured at `89675e6`. This file is tracked at later revisions and `master` moves repeatedly during concurrent work, so re-verify a line number against the revision you are on before acting on it.

**Prepared by:** general development agent, Phase 1 (read-only verification) then Phase 2 (consolidation).
**Reference state at verification:** `master` @ `89675e6` ("docs(issues): close KI-33 and KI-36 on the commits that fixed them").
**Method:** every claim was tested with read-only commands — `git log`, `git show`, `git grep`, `git ls-files`, `git status`, `git diff --stat`, `ls`, file reads, and `vendor/bin/pint --test`. No file was edited, no commit made, no stash, no `git checkout`/`switch`/`reset`, no migration run, no database write, no test-suite run.

### 0. Input caveat — read this before trusting the session column

The eight session summaries were not present in the request. What arrived was a minimum claim set derived from them, plus four inline session labels (1, 3, 6, 7, 8). Consequences:

- Claims are attributed to a session only where the request text named one. Unattributed claims are marked `unattributed`.
- Two requested cross-session comparisons could not be performed at all, because they require prose that was not delivered: whether session 1's "cherry-pick two docs commits" and session 3's "port three things" describe the same resolution, and the content of session 7's "three unstaged edits" as described.
- Some claims were stated as facts about a session's own state (a gate result, a worktree path) and are verifiable only against the state that exists now. Where the current state contradicts the claim, the claim is marked REFUTED and the possibility that it was true at an earlier sha is recorded rather than dismissed.
- The tree moved during this pass. `master` advanced to `89675e6` before it began, and `KNOWN-ISSUES.md` anchors observed in this session shifted (`KI-10` at line 566 earlier, line 575 now). Every line citation below is valid at `89675e6` only.

### 1. Verification table

Verdicts: CONFIRMED, REFUTED, PARTIAL (true in one place/sha, not another), UNVERIFIABLE (needs a state change or missing input).

#### 1.1 Schema and migrations

| #       | Claim                                                                | Verdict                                 | Evidence                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 |
| ------- | -------------------------------------------------------------------- | --------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1.1.1   | `umamusume_profiles` missing from the live DB                        | UNVERIFIABLE                            | The live database cannot be opened read-only (§1.2.2). The migration exists in both the worktree and `HEAD`: `database/migrations/2026_09_29_182820_create_umamusume_profiles_table.php`. Whether the table exists in the running database is untested.                                                                                                                                                                                                                                                  |
| 1.1.2   | Two migrations create `umamusume_profiles` with divergent columns    | PARTIAL                                 | One is tracked (`…_182820_…`, on disk and in `HEAD`). The second (`…_2026_09_30_120000_…`) exists **only inside unreferenced commit `992016d`** and on no disk path in `D:/Projects`. Column sets diverge — see §1.1.3.                                                                                                                                                                                                                                                                                  |
| 1.1.3   | Divergent columns, listed                                            | CONFIRMED                               | `182820` (tracked): `umamusume_id`, `name_ja`, `va_ja`, `va_en`, `birth_year`, `birth_month`, `birth_day`, `height`, `three_sizes_b`, `three_sizes_h`, `three_sizes_w`, `source_url`, `snapshot_path`, `fetched_at`, `source_timezone`, `is_manual`. `120000` (`992016d`): same minus `name_ja`, and `three_sizes_b/h/w` replaced by `height_cm`, `bust_cm`, `waist_cm`, `hip_cm`. So the two differ by one dropped column (`name_ja`) and a four-column body-measurement regroup.                       |
| 1.1.4   | Three class names have two definitions each                          | REFUTED                                 | Scanning every `class`/`interface`/`trait`/`enum` declaration under `app/` yields exactly one repeated short name: `TrainingRunController`. The two files declare different namespaces — `app/Http/Controllers/TrainingRunController.php:5` `namespace App\Http\Controllers;` and `app/Http/Controllers/Api/V1/TrainingRunController.php:5` `namespace App\Http\Controllers\Api\V1;`. No redeclaration, no autoload collision. Count found: one, not three.                                              |
| 1.1.5   | `132304_add_is_manual_to_scenario_races_table.php` is untracked      | REFUTED                                 | No file of that name exists on disk or in any index. The real, tracked migration is `database/migrations/2026_09_27_183245_add_is_manual_to_scenario_races_table.php`. The claim's timestamp prefix is wrong and its tracked/untracked status is inverted.                                                                                                                                                                                                                                               |
| 1.1.6   | Worktree has 20 migrations versus 17 in `HEAD`                       | REFUTED                                 | Main worktree: 35 on disk, 35 in its `HEAD` tree, zero untracked additions. `C:/Users/exatf/AppData/Local/Temp/kilo/detail-page`: 34/34. `.kilo/worktrees/cypress-cardamom`: 33/33. In every worktree, disk equals `HEAD`. The 20/17 pair does not exist anywhere observable.                                                                                                                                                                                                                            |
| 1.1.7   | `StoreTurnEntryRequest.php:49-53` still validates `between:0,1200`   | CONFIRMED                               | Lines 49–53 are exactly `speed`, `stamina`, `power`, `guts`, `wit`, each `['required', 'integer', 'between:0,1200']`. The docblock still asserts the 0..1200 range. `ADR-0002` accepted 0..2000 and `ADR-0003` requires the bound be read from the run's own `scenarios.hard_cap`, so both the value and the shape gap remain open.                                                                                                                                                                      |
| 1.1.8   | The `sp` bound's source is unstated                                  | REFUTED as stated; superseded in code   | `StoreTurnEntryRequest.php:54` now reads `'sp' => ['nullable', 'integer', 'min:0']`. The upper bound is **gone**. Earlier in this same session the same line read `between:0,1200`, so it changed during this working period. Note the direction: the previously escalated owner ruling recommended keeping a provisional ceiling with a source-less marker; the code instead removed the ceiling, which is a different option. No source now needs to back a 1200 SP cap, because no cap is asserted.   |

#### 1.2 Data and database state

| #       | Claim                                                                 | Verdict                                | Evidence                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   |
| ------- | --------------------------------------------------------------------- | -------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1.2.1   | `database/database.sqlite` is a 0-byte main file plus a 1.67 MB WAL   | CONFIRMED                              | `database/database.sqlite` 0 bytes (15:52), `database.sqlite-wal` 1,672,752 bytes (15:17), `database.sqlite-shm` 32,768 bytes (01:43). Also present: `database.sqlite.bak` 4,096 bytes, `test_coherence.sqlite` 225,280 bytes. Durability consequence: every committed row currently lives only in the WAL, not in the main file.                                                                                                                                                                                                                                                                          |
| 1.2.2   | `PRAGMA integrity_check` on the shared dev DB                         | UNVERIFIABLE without a state change    | `sqlite3` CLI is absent. Node's `node:sqlite` with `readOnly: true` returns `disk I/O error` (errcode 2570) — the expected result for a read-only connection to a WAL-mode database whose WAL must be replayed, **not evidence of corruption**. Copying main + `-wal` + `-shm` to a temp path and opening the copy yields zero tables, which is a torn-snapshot artifact of copying a hot database, also not evidence of corruption. Closing this item needs either a consistent snapshot (`VACUUM INTO` or the backup API — a write), or a read through the running application. Neither was performed.   |
| 1.2.3   | Shared dev DB holds 410 catalogue rows                                | UNVERIFIABLE                           | Same obstacle. Additionally unestablished: no migration-derived table named `race_catalog_slots` was confirmed in a readable database during this pass. The readable `test_coherence.sqlite` (24 tables) contains `scenario_slots` and `scenarios` but has **zero rows** in each, so it cannot corroborate the figure either.                                                                                                                                                                                                                                                                              |
| 1.2.4   | 107 cards, 0 with lists                                               | UNVERIFIABLE                           | Same obstacle. No readable database exposes a `character_cards` table.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     |
| 1.2.5   | Provenance artefacts untracked, `.scratch-uma/` shows `??`            | PARTIAL, changed during this session   | Zero files under `.scratch-uma/` are tracked. It is currently **ignored**, not merely untracked: `git check-ignore -v .scratch-uma/cited/manifest.json` returns `.gitignore:88:/.scratch-uma/`, and `git status --porcelain .scratch-uma/` now prints nothing. Earlier in this session the same directory did appear as `??`. Either way the substantive concern stands: the Tier-0 exports cited by `ADR-0002` live outside version control and outside history.                                                                                                                                                |
| 1.2.6   | `ADR-0002:86` cites hash `61b7c51c`; does it match anything           | CONFIRMED                              | `docs/adr/0002-scenario-caps-exceed-validation-bound.md:86` reads "manifest key `scenarios`, hash `61b7c51c` at fetch time, fetched 2026-09-27". The string `61b7c51c` is present in `.scratch-uma/cited/manifest.json`. It is a dataset manifest hash, not a git object — `git log -1 61b7c51c` fails as an unknown revision, which is expected and not a defect.                                                                                                                                                                                                                                               |

#### 1.3 Commits and branches

| #     | Claim                                                                                  | Verdict                             | Evidence                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                |                                                                                                                      |
| ----- | -------------------------------------------------------------------------------------- | ----------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------- |
| 1.3.1 | `feat/umamusume-detail-page` carries a second `create_umamusume_profiles_table`        | REFUTED — the branch does not exist | Refs present: `master` `89675e6`, `feat/catalog-detail-page` `e22d05e`, `feat/catalog-roster-and-trainee-selector` `7cc6283`, plus `origin/master` `2033434`, `origin/docs/audit-remediation`, `origin/fix/frontend-audit-2026-09-28`. No ref matching `umamusume-detail` exists locally or remotely. The second migration is reachable only as commit `992016d`, which no branch contains.                                                                                                                                                                                                                                             |                                                                                                                      |
| 1.3.2 | `992016d` exists; what is it; does dropping it orphan anything                         | CONFIRMED with a warning            | `992016d` "feat(profiles): the umamusume_profiles sibling table and its schema guard", 2026-09-30 04:34:33, 316 insertions across five files: `app/Models/UmamusumeProfile.php`, `database/factories/UmamusumeProfileFactory.php`, `tests/Feature/CharacterProfileTest.php`, `ARCHITECTURE-ESSENTIALS.md`, and the `…_120000_…` migration. `git merge-base --is-ancestor 992016d HEAD` returns no, and `git branch -a --contains 992016d` returns nothing. It is **already unreferenced**: no branch protects it, so it survives only in reflog and will be lost to `git gc`. "Dropping" it is not an available action; rescuing it is. |                                                                                                                      |
| 1.3.3 | `master` has `555b0cb` plus §B and §D                                                  | PARTIAL                             | `555b0cb` ("feat(catalog): character detail page with profile source", 2026-09-30 03:44:33) is an ancestor of `HEAD`: CONFIRMED. "§B" and "§D" cannot be resolved — they are section markers internal to a session summary that was not supplied. UNVERIFIABLE.                                                                                                                                                                                                                                                                                                                                                                         |                                                                                                                      |
| 1.3.4 | Session 8's 11 unpushed commits versus a peer thread's pushed work                     | PARTIAL                             | `git rev-list --count origin/master..HEAD` returns a positive count and `HEAD..origin/master` returns **0** — the count itself drifts every turn and is not load-bearing. Session 8's figure of 11 was not reproduced at any revision measured. `git rev-list --count HEAD..origin/master` = **0**: nothing exists on the remote that is missing locally. `origin/master` remains `2033434`. The 11 figure cannot be reproduced at any observed state.                                                                                                                                                                                  |                                                                                                                      |
| 1.3.5 | `calendarCells` appears 0 times in `HEAD`'s `TrainingRun.php` and once in the worktree | REFUTED                             | `git show HEAD:app/Models/TrainingRun.php \                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             | grep -c calendarCells` = 1. The working file = 1. Identical in both; there is no uncommitted instance of the symbol. |
| 1.3.6 | `tests/Feature/RaceSlotPanelComposerTest.php` is untracked                             | REFUTED                             | The file exists on disk and `git ls-files --error-unmatch` succeeds: it is tracked.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     |                                                                                                                      |

#### 1.4 Docs and citations

| #       | Claim                                                                                                         | Verdict                                           | Evidence                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           |
| ------- | ------------------------------------------------------------------------------------------------------------- | ------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1.4.1   | `DESIGN.md:191` cites `KNOWN-ISSUES.md:855-861`; the range now sits inside KI-10 and KI-20 begins at `:987`   | PARTIAL — the defect is real, one anchor is off   | `docs/design-research/DESIGN.md:191` reads "The four ratios KI-20 records in `KNOWN-ISSUES.md:855-861` do not reproduce from the hex values they". At `89675e6`, `KNOWN-ISSUES.md` headings are `KI-10` at **575**, `KI-20` at **994**, `KI-33` at 1498, `KI-36` at 1678. So lines 855–861 do fall inside KI-10's body, and their content is the Trackblazer grade-point denominator discussion ("`standard` is rendered and the code says so at `app/Models/TrainingRun.php:378-385`"), which has nothing to do with shop contrast ratios. The citation is wrong. KI-20 begins at 994, not 987.   |
| 1.4.2   | D-289's three citations are closed by `afd3ae1`, `9300e6c`, `a18d77a`                                         | PARTIAL                                           | All three exist and all are constraint/doc commits: `afd3ae1` 2026-09-29 20:18 "docs(constraints): close D-289's own first ins…", `9300e6c` 20:22 "docs(constraints): drop the last line-number p…", `a18d77a` 20:33 "docs(register,verification): cite by heading i…". The stronger half of the claim — that each touches D-289 **and nothing else** — was not verified, because it needs per-commit file lists.                                                                                                                                                                                  |
| 1.4.3   | ADR-0008 / `ARCHITECTURE.md` §3 / ESSENTIALS / D-30 still say twelve columns                                  | PARTIAL                                           | Two of the four. Hits: `docs/adr/0008-character-card-catalog-layer.md:235` and `docs/adr/0012-card-detail-fields-and-images.md:7` ("twelve columns `ADR-0008` named"). No "twelve columns"/"12 columns" string exists in `ARCHITECTURE.md`, `ARCHITECTURE-ESSENTIALS.md`, or `CONSTRAINTS.md` D-30. The claim also omits ADR-0012, which is the second stale site.                                                                                                                                                                                                                                 |
| 1.4.4   | KI-34 is reserved but not landed                                                                              | CONFIRMED                                         | `KNOWN-ISSUES.md` has no `## KI-34` heading; the register jumps KI-33 (:1498) to KI-36 (:1678). The reservation is documented at `docs/design-research/verification/design-pass-trainee-detail-2026-09-29.md:32` "**KI-34 is deliberately reserved, not filed.**" and §5 at :110. That file is tracked, so the record is durable.                                                                                                                                                                                                                                                                  |
| 1.4.5   | `docs/scenarios/09-global-race-calendar.md:59` — read the line, name the fix                                  | PARTIAL                                           | The file exists and line 59 reads "Three spot-checks confirm the labels for **those three tiers**, not that every row's grade code is correctly assigned across the pool — the map is trusted, the per-row assignment is not independently…". Which specific fix was intended is a property of the missing session text. UNVERIFIABLE as to the fix.                                                                                                                                                                                                                                               |

> **Forward correction to 1.4.1, appended 2026-09-30 at `318277a` by the documentation inventory.** The row's own conclusion is wrong about one thing, and it is the entry the correction was supposed to be about. `KNOWN-ISSUES.md:855-861` does not fall inside KI-10's body. Re-read at `89675e6`, the register headings run KI-10 at 575, KI-11 at 657, KI-12 at 696, KI-13 at 740, KI-14 at 794, **KI-15 at 837**, KI-17 at 871, and KI-20 at 994, so 855 to 861 sits inside **KI-15**, the three-Grade-Point-tracks entry. The quoted content confirms it: the `standard` denominator and `app/Models/TrainingRun.php:378-385` are KI-15's subject, not KI-10's. Two parts of the row stand as written. `DESIGN.md:191`'s range is genuinely wrong, and KI-20 does begin at 994 rather than the 987 that the incoming claim asserted; that refutation was already in the Evidence column, so no number in this file needs correcting today. What needs correcting is the attribution, and the durable lesson is in §6.

#### 1.5 Gates and test state

| #       | Claim                                                        | Verdict                   | Evidence                                                                                                                                                                           |
| ------- | ------------------------------------------------------------ | ------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1.5.1   | Session 8's gate result: 2 failed / 227 passed / 2 skipped   | UNVERIFIABLE in Phase 1   | Reproducing it requires running Pest, which writes to a test database. The fence forbids database writes, so the suite was not run.                                                |
| 1.5.2   | Session 6's suite: 676 passed / 2 skipped / 0 failed         | UNVERIFIABLE in Phase 1   | Same reason. Note the two counts differ by a factor of three, which is itself worth reconciling once a suite run is authorized.                                                    |
| 1.5.3   | Pint fails on `app/Models/TrainingRun.php`                   | REFUTED at this sha       | `vendor/bin/pint --test app/Models/TrainingRun.php` → `PASS … 1 file`. This check is read-only, so it was run. It says nothing about session 8's own worktree or an earlier sha.   |
| 1.5.4   | `SkillAutomationTest` baseline is 6 failures                 | UNVERIFIABLE              | A runtime property; needs an authorized test run.                                                                                                                                  |

#### 1.6 Dirty and dirty-adjacent files

| #       | Claim                                                                     | Verdict                                      | Evidence                                                                                                                                                                                                                                                                                                                                                                                                                                            |
| ------- | ------------------------------------------------------------------------- | -------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1.6.1   | `app/Services/DataPipeline/PipelineRunner.php` is dirty                   | REFUTED now                                  | `git status --porcelain` at `89675e6` lists **no** ` M ` entries at all. Every modification reported by the sessions has since been committed by the concurrent work.                                                                                                                                                                                                                                                                               |
| 1.6.2   | `SKILLS-GAPS.md` and `resources/views/skills/index.blade.php` are dirty   | REFUTED now                                  | Same: zero tracked-file modifications. Recent commits plausibly absorbed them — `4902f1d` "fix(runs): repeat the skills row per skill and name each control (KI-36, KI-33's repeater)" and `dd90330` "feat(cards): store a trainee's own skill lists…".                                                                                                                                                                                             |
| 1.6.3   | Session 7's three unstaged edits                                          | REFUTED now, and unresolvable as described   | Current untracked set is entirely documentation or images, with no source file among it: three `docs/` specification files with spaces in their names, `FRONTEND-BRIEF-AUDIT.md`, `FRONTEND-SPEC-DIVERGENCE.md`, `MECHANICS-TRANSLATION-TRIAGE.md`, `TASK-16-RUN-VIEW-FRAME-BRIEF.md`, two `docs/requests/reports/2026-09-30-*.md`, and `docs/vibe_images/`. None is a PHP source file, so the three claimed unstaged edits cannot be identified.   |
| 1.6.4   | Scenario-key split: `ura-finals` versus `ura_finale`                      | REFUTED on `master`                          | `git grep -l "ura-finals"` → **0 files**. `git grep -l "ura_finale"` → **60 files**. There is one canonical spelling in tracked content; the hyphenated variant exists only in untracked or non-delivered material.                                                                                                                                                                                                                                 |

### 2. Cross-session conflicts, and the true state

| Conflict                                                                                                                                  | True state                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             | Cites                      |
| ----------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------- |
| Session 1: `umamusume_profiles` missing from the live DB. Session 3: two migrations create `umamusume_profiles` with divergent columns.   | Both are incomplete, and they are not actually in conflict. `master` carries exactly **one** tracked profiles migration (`…182820…`). A **second** migration exists, but inside unreferenced commit `992016d`, at no disk path and on no branch; its column set genuinely diverges (drops `name_ja`, regroups body measurements into `height_cm`/`bust_cm`/`waist_cm`/`hip_cm`). Whether the table exists in the running database is **separate and still unverified**, because the database cannot be opened read-only. Session 1's claim is an unverified database observation; session 3's is a half-correct history observation.   | §1.1.1–3, §1.2.2, §1.3.1   |
| Session 1: do not merge `feat/umamusume-detail-page`, cherry-pick two docs commits. Session 3: keep `master`, port three things.          | **Cannot be compared.** Both reference content from summaries that were not supplied, and the branch one of them names does not exist as a ref. What can be said: `master` is strictly ahead of the remote with nothing behind it, so "keep `master`" is the only option consistent with observed history, and no merge in either profiles direction is currently possible because no branch carries the second migration.                                                                                                                                                                                                             | §1.3.1, §1.3.4, §0         |
| Session 8: 11 unpushed commits. Another thread: work already pushed.                                                                      | `master` is ahead of `origin/master`; **0** commits exist on the remote that are missing locally. There is nothing to reconcile in the pull direction; the only real exposure is local work with no remote copy. The ahead-count drifts (it moved 33 → 40 inside one turn) and is deliberately not recorded.                                                                                                                                                                                                                                                                                                                           | §1.3.4                     |
| Session 8: Pint fails on `TrainingRun.php`. Current tree.                                                                                 | Pint passes at `89675e6`. Either it was fixed in the interim, or session 8 was describing a different worktree. `calendarCells` is identical in `HEAD` and on disk, which supports the "interim commits" reading over the "different worktree" one.                                                                                                                                                                                                                                                                                                                                                                                    | §1.5.3, §1.3.5             |
| Migration-count claims: 20 versus 17.                                                                                                     | No worktree shows either number. Disk equals `HEAD` in all three worktrees (35/35, 34/34, 33/33). The claim appears to describe a state that no longer exists, or a count taken from a filtered subset.                                                                                                                                                                                                                                                                                                                                                                                                                                | §1.1.6                     |
| `KI-20` starts at 987 versus at 994.                                                                                                      | 994 at `89675e6`. Both agree the `DESIGN.md:191` range is wrong; only the target line number differs, and the file has moved anchors twice within this session.                                                                                                                                                                                                                                                                                                                                                                                                                                                                        | §1.4.1                     |

### 3. Decision queue

Ordered by how much downstream work each one unblocks. No decision is made here.

1. **What is the canonical `umamusume_profiles` shape?** Question: does the profile table carry `name_ja` plus `three_sizes_*`, or `height_cm`/`bust_cm`/`waist_cm`/`hip_cm` without `name_ja`? Options: (a) keep the tracked `…182820…` and treat `992016d` as withdrawn; (b) adopt `992016d`'s shape, which needs a fresh migration since the tracked one is already in history; (c) a third shape. Closes: the profiles conflict (§2), session 1's missing-table question, and the parser/model/view work waiting on it. Cost of the recommended-by-position option: unverifiable from here — (a) is the only one needing no new migration, and `ADR-0013` was withdrawn at `5a50900` with `ADR-0012` Decision 4 named as the profile authority, which points at (a).
2. **Rescue or release `992016d`?** Question: is the unreferenced commit worth a branch, or is it abandoned? Options: (a) `git branch <name> 992016d` now to stop GC exposure; (b) declare it superseded and let it expire. Closes: §1.3.2 and the `feat/umamusume-detail-page` phantom reference. Cost of (a): one local ref, reversible, no history change. This is time-sensitive: an unreferenced commit has no long-term guarantee.
3. **Which database is the development view?** Question: the live `database/database.sqlite` cannot be inspected read-only, and its entire content sits in a WAL. Options: (a) authorize a consistent read-only-safe snapshot (`VACUUM INTO` to a temp path, a write to a new file only); (b) restart the app so the WAL checkpoints, then read; (c) accept counts as reported and verify through the running UI. Closes: §1.2.1–4, four otherwise unanswerable data claims. Cost of (a): one temp file, never touches the live database.
4. **Is the `sp` bound decided?** Question: the ceiling was removed in code (`min:0`) without the ruling that was requested. Options: (a) accept `min:0` as the ruling; (b) restore a provisional ceiling with a source-less marker; (c) set a cited value. Closes: §1.1.8 and the wider stat-bound workstream. Cost of (a): zero code change, one sentence of documentation.
5. **Widen the stat bound, and to what shape?** Question: `…:49-53` still asserts 0..1200 while `ADR-0002` accepted 2000 and `ADR-0003` requires reading the run's own `scenarios.hard_cap`. Options: flat 0..2000, or per-scenario lookup. Closes: the item deferred to a dedicated session. Cost: it breaks `TrainingRunTest.php:62` ("rejects a stat above the 1200 cap") and touches 10 test files asserting 1200, so it needs a deliberate test change, not a silent one.
6. **Push the local commits, or keep working unpushed?** (`master` is ahead of `origin/master` with nothing behind; the count is not decided here and drifts every turn.) Closes: §1.3.4 and the durability exposure. Cost: pushes shared state, so it needs explicit authorization.
7. **Which docs claims about D-289 and the twelve-column sites are authoritative?** Cost: `git show --stat` on three commits plus a decision on ADR-0012:7, which nobody has flagged yet.
8. **What happens to the stash at `7bbe4d5`?** Created 2026-09-30 04:15:27, "stale forks of branch commits + broken Blade cardless band (triage 2026-09-30)", carrying `CONSTRAINTS.md`, `PLAN.md`, `app/Http/Controllers/CatalogController.php`, `app/Services/DataPipeline/PipelineRunner.php`, `package.json`, `package-lock.json` and `resources/views/catalog/index.blade.php`. Options: keep, pop, or drop. Nobody has named an owner for it. It has no overlap with session 7's three files, so it is not the explanation for §1.6.3.
9. **Closed — no decision needed.** §3.2 (rescue or release `992016d`) is resolved: the ten-commit chain is protected by the ref `rescue/profiles-chain` at `8ffab63`, so the GC exposure described at §1.3.2 is over. The ref was created under an earlier authorization, not by this session, and it is not to be re-litigated here. What remains open on the profiles question is §3.1, the lineage pick, which now gates the schema pick.

#### 3.9 — Correction appended 2026-09-30 (development-agent Phase A), which collapses §3.1 and §3.2 into one decision

The verdicts above are left exactly as written; this records what a later read-only pass found underneath them.

§3.2 was framed as rescuing one loose commit that "survives only in reflog", and §3.1 as a tracked migration plus a second schema variant. Neither is the shape of the state. The unreachable work is an **eight-commit linear chain** — `92670ff` (ADR-0013) → `992016d` (profiles table and schema guard) → `1a07d24` (parser, store, fifth route) → `d776d08` → `c8f3a70` → `855bcf4` → `fa93cae` (review Majors closed, rollback proven) → **tip `8ffab63`** — forked from master at `8acb678` on 2026-09-30 between 04:13 and 05:59. No reflog anywhere protected it: not `.git/logs`, not `.git/worktrees/*/logs`, and `ORIG_HEAD` named something else. It survived only as loose objects, so a single `git gc --prune=now` or `git repack -A -d` would have destroyed all eight commits immediately, not in thirty days. The default `gc.pruneExpire` of two weeks from object creation put real exposure at roughly 2026-10-14; `gc.auto` was nowhere near its trigger (451 loose objects against 6770, five packs against fifty).

Rescuing `992016d` alone would have preserved two of eight commits. With the owner's authorization the chain was rescued at its tip: `git branch rescue/profiles-chain 8ffab63`, verified by `git rev-list rescue/profiles-chain --not --all --count` returning 0, and reversible with `git branch -D`. No history was changed and no working tree was touched.

That changes §3.1 from a schema question into a product pick. There are two complete parallel implementations of the profiles feature. `master` carries `555b0cb` and everything after it, tracked and published. The chain carries the same four PHP classes under the same fully-qualified names — `App\Models\UmamusumeProfile` is declared at line 67 on master and line 48 on the chain — with a divergent migration shape (`name_ja` plus `three_sizes_b/h/w` against `height_cm`/`bust_cm`/`waist_cm`/`hip_cm`), its own `docs/adr/0013-character-profile-source.md`, and two review-fix commits. A read-only merge simulation of `master` with the chain tip yields **seven conflicting paths**: add/add on `app/Models/UmamusumeProfile.php`, `app/Actions/StoreCharacterProfiles.php`, `app/Services/DataPipeline/Parsers/GametoraCharacterProfileParser.php`, `database/factories/UmamusumeProfileFactory.php`, `docs/adr/0013-character-profile-source.md` and `docs/data/2026-09-30-characters-source-probe.md`, plus a content conflict in `app/Services/DataPipeline/PipelineRunner.php`. So §3.1 and §3.2 are no longer separable: adopting either side's shape is a merge decision against a real second implementation, not a choice between a column list and a rumor.

On §1.1.4, the refutation answered a narrower question than session 3 asked. Against the merge state session 3 described, four PHP class files do appear on both sides, so the count was close; the mechanism was not. Git reports add/add conflicts rather than two definitions surviving in one tree, so there is no autoload collision to fix. PARTIAL is the accurate verdict now.

On §1.1.8 and §3.4, the premise was wrong and the finding survives in a different shape. The `sp` ceiling was not removed during the audit's working period. `9b774f9` (IzzatFirdaus, 2026-09-28 14:19:04, `feat(schema,providers): Preference store, ScenarioSlot matrix, scenario-key validation`) changed `'sp' => ['nullable', 'integer', 'between:0,1200']` to `min:0` two days earlier, and that commit is the tip of `origin/docs/audit-remediation`, so the change is already published. No live session overrode a pending ruling. What remains true is a separate hygiene finding: a bound change rode inside a three-purpose feature commit whose message never names SP, bounds or ceilings.

#### 3.9.1 — The rescue ref was renamed to `archive/` on 2026-10-01 (owner ruling; the last unexecuted item of the §3.2 resolution)

**Everything above keeps the name it was written under.** `rescue/profiles-chain` appears at §3.9, in the
§6 Phase-A measurement row and in the merge-discipline row, and those sentences are left verbatim per the
erratum convention now stated in `docs/adr/README.md` — they were true of the ref under the name they use, and
rewriting them would destroy the record that the ref existed under that name at all. This subsection is the
correction.

The rename, executed once:

```text
git branch -m rescue/profiles-chain archive/profiles-chain
```

Lowercase `-m`, so the rename is a move, not a forced overwrite. Verified after the fact rather than assumed
from a clean exit:

- `git rev-parse archive/profiles-chain` → `8ffab6363492e81f50043f90460f2909f94a3767`, the same tip §3.9 records
  as the rescue anchor. The commits did not move.
- `git rev-parse --verify rescue/profiles-chain` → `fatal: Needed a single revision`. The old name is gone, so
  nothing can silently keep resolving to it.
- `git branch -v` lists `archive/profiles-chain 8ffab63` and no `rescue/` row.

**A correction to §3.9's own verification, found while checking this rename, and the more important half of
the entry.** §3.9 records that the rescue was "verified by `git rev-list rescue/profiles-chain --not --all
--count` returning 0", and reads that as nothing-at-risk. **That command cannot return anything but 0 for any
branch that exists, because `--all` includes the branch being measured.** The check is self-satisfying, which
puts it in the same class as the `->check()` no-op and the schema-less test elsewhere in this file: it reports
success while proving nothing. Measured properly — excluding the ref under test — the truth is the opposite of
what the number implied:

```text
git branch --contains 8ffab63                ->  archive/profiles-chain   (and nothing else)
git rev-list archive/profiles-chain --not <every other head and remote> --count  ->  8
```

So **the eight commits are unique to this single ref.** No other branch protects them, which means §3.9's
exposure analysis stands in full — one `git gc --prune=now` while the ref is absent destroys the chain, and
the `gc.pruneExpire` horizon it computed (roughly 2026-10-14) is live — and it means **the rename was not a
zero-risk operation the way this subsection first claimed.** It was safe only because `-m` moves the ref
atomically rather than deleting and recreating it, and because the tip sha is recorded above and below. The
first draft of this entry asserted "the chain is reachable through
`feat/catalog-roster-and-trainee-selector`… so renaming the ref carried no GC risk." That sentence was wrong,
inferred from `--not --all` returning 0, and it is the exact error the ref has now been mis-stated around
three times. Anyone re-running that verification should use the excluded form; the inclusive form will always
say the chain is safe.

**Recovery anchor, now load-bearing rather than belt-and-braces:**
`git branch archive/profiles-chain 8ffab6363492e81f50043f90460f2909f94a3767` recreates the tip under either
name from a sha, which is the only durable form given that no other ref holds these commits. Record the sha,
not just the name.

**Why the rename is worth a record at all:** this ref has now been miscited from both directions inside one
session. §6's merge-discipline row records a pass that reported `rescue/profiles-chain` as peer work landing
mid-turn when it had existed since an earlier authorization, and a later ruling cited `archive/profiles-chain`
as a rename that had failed to land when no rename had ever been queued. Both are the same error — reasoning
about a ref's state from the prose in front of you instead of from `git branch -a`. The check that settles it
 costs one command and was run before and after here.

**Appended 2026-09-30 after the lineage check — the schema pick and the lineage pick are one decision, and the lineage pick comes first.** `92670ff`'s parent is `7cc6283`, the tip of `feat/catalog-roster-and-trainee-selector`, so the profiles chain continues that branch rather than forking from master. The chain is therefore ten commits off an unmerged local branch that is 2 ahead of and 17 behind master, and the real question is whether the roster+profiles lineage merges as a unit, is rebased onto master with the roster commits dropped, or is abandoned. `name_ja` plus `three_sizes_*` against `height_cm`/`bust_cm`/`waist_cm`/`hip_cm` cannot be answered before that, because the two candidate trees already carry divergent migrations and the schema lives inside the tree choice.

### 4. Blocked without a decision

**Blocked on a human decision:**

- The profiles schema question (§3.1). No agent can pick it: it is a product-data question about which source fields are true, and the two candidate shapes disagree about a real column, `name_ja`.
- Rescuing `992016d` (§3.2) — creating a branch is not destructive, but it is a claim that the work is wanted, and only the owner knows.
- The `sp` bound (§3.4) and the stat-bound shape (§3.5), because both require changing or deleting an existing intentional test.
- Pushing (§3.6), because it touches shared state.
- Any test-suite run, because it writes to a database and the fence is explicit.
- **How sessions are to be isolated from each other's database, added 2026-10-01.** Three live slices share one working tree, one `database/database.sqlite`, and no announcement channel. A peer rebuilt the shared database mid-pass in order to re-seed it (see §6's escalation row), which was legitimate under their own fence and destructive to another session's measurement. The candidate policies are not equivalent and none is an agent's to choose: per-slice git worktrees, one scratch database per session with a naming rule, or a commit-discipline rule that forbids `migrate:fresh` against the shared file in any session. This is not about blame — the re-seed was the peer's intent — it is that shared state changed under a running measurement and no control prevented it. Deciding "no" is a valid answer; leaving it undecided means the next session infers whatever it happens to notice first.

**Blocked on a peer session, not on a decision:**

- The eight session summaries themselves. Two requested comparisons cannot be attempted without them (§0).
- Whether session 8's gate and Pint results were real: needs that session's sha and worktree, which it has not recorded anywhere reachable.
- Line-number citations across `KNOWN-ISSUES.md` and `DESIGN.md`: `KI-10` moved from 566 to 575 during this session, so any session citing an anchor needs to re-cite after the concurrent edits settle.
- **`AGENTS.md:25`'s citation notation, added 2026-10-01 (Slice 6, finding P-1 / N-4).** The rule says "cite a PRD requirement (FR-x / US-x)", but the PRD writes its markers as `- C-1:` under `### FR-C:` — so `FR-C-1` appears nowhere in the document it names, and 26 distinct `FR-X-N` ids are cited outside the PRD as ungreppable strings. Every correct citation produced by following the rule is therefore hard to verify. Suggested fix is one clause stating the marker form and the strip-the-prefix resolution rule. **Owner's call, not an agent's** — it is governance text, and the rejected alternative (re-prefixing ~30 PRD list markers) would make the PRD the inconsistent document against every citation outside it. No wording applied.
- **Slice 7 — PRD revision for three uncited shipped columns, queued 2026-10-01, not opened (Slice 6 findings P-2 / P-3 / P-4 / N-2).** `docs/adr/0003…:104` planned `FR-C-6` for Energy and Fans and `FR-C-7` for event and race logging; neither requirement was ever written. `PRD.md:132` then used `C-6` for the deck under `ADR-0014`, so one number means two things. The consequence: `turn_entries.energy`, `.mood` and `.fans` are real columns, collected by the guided form and emitted by `export()`, with **no PRD requirement** — across 191 PRD lines the three words appear once, inside `OQ-5`'s aside. `AGENTS.md:25` requires a citation per column, so three shipped columns violate a rule that has been enforced prospectively only. Writing the requirement is a product statement made after the fact, which is exactly what Slice 6's fence existed to keep out; it belongs in a revision dispatch, in the same queue as the four deprecated-PDF decisions and the three Request-D flags. **Queued, not acted on.**
- **R-02's em-dash ban versus the documentation corpus, raised 2026-10-01 and left unresolved by the owner's ruling.** `antislop-copywriting` R-02 forbids the em dash outright; roughly forty files under `docs/` use it as ordinary prose rhythm, including this document, the deprecated-PDF review, and the Slice 6 deliverable. Three readings were put, and none is an agent's to pick: the rule is right and the corpus drifted, which makes remediation a project and forces the rule to be enforced or amended; or R-02's scope is shipped copy and `docs/**` is outside it, which means the rule's own text should say so; or the rule targets AI-slop dash clusters and is currently shaped as a blanket ban. Until one is chosen, **every writer on this repo is guessing**, which is the actual cost of leaving it open. Interim disposition as ruled: keep house style, surface the conflict rather than resolve it silently, and avoid `--` inside a block whose comment marker is already `--`, where it reads as a nested comment.

### 5. Safe now

Nothing in this list was executed. Each is verifiable or reversible on its own terms.

1. Re-point `DESIGN.md:191` at the KI-20 range that actually holds the four ratios, instead of `KNOWN-ISSUES.md:855-861`. Pure citation fix, no behavior change, no test coverage.
2. Add a `docs/vibe_images/` rule to `.gitignore`. Nine untracked PNGs currently match no rule because line 100 is root-anchored `/vibe_images/`. See the caution in §7.4 before touching this file.
3. Delete or move the inert line 101 in `.gitignore`. It parses as nothing and duplicates line 88's coverage.
4. Correct the twelve-column statements at `ADR-0008:235` and `ADR-0012:7`, and record that `ARCHITECTURE.md`, ESSENTIALS and D-30 do not carry the claim at all.
5. Record in `KNOWN-ISSUES.md` that the KI-34 reservation currently has no register entry, so a reader cannot mistake the gap for lost work. The reservation text already exists and is tracked.
6. Collect the per-commit file lists for `afd3ae1`, `9300e6c`, `a18d77a` to finish §1.4.2. Read-only.
7. Commit the untracked documentation files listed in §1.6.3 that are finished, and confirm the three with spaces in their names and the two `docs/requests/reports/` files are intended to be tracked. This is a commit, so it needs authorization under the fence.

### 6. Open verification gaps

| Gap                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         | What closes it                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     |                                                                                                                                                                                                                                                                                                             |
| ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| §1.1.1, §1.2.1–4 — every live-database claim                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                | A consistent snapshot opened read-only, or a read through the running app. Copying the hot files does not work; it yields a torn snapshot that reads as empty.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     |                                                                                                                                                                                                                                                                                                             |
| §1.5.1, §1.5.2, §1.5.4 — all three gate counts                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              | An authorized `php artisan test --compact` run against a named sha, in a worktree that no other session is using.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  |                                                                                                                                                                                                                                                                                                             |
| §1.3.3 — "§B and §D"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        | The session 1 or 3 text that defines those sections.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               |                                                                                                                                                                                                                                                                                                             |
| §1.4.5 — the fix intended at `scenarios/09:59`                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              | The session text naming the fix.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   |                                                                                                                                                                                                                                                                                                             |
| §1.6.3 — session 7's three unstaged edits                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   | The session's list of the three files, plus the sha they were dirty at.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            |                                                                                                                                                                                                                                                                                                             |
| §1.4.2 — "touches D-289 and nothing else"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   | `git show --stat` on the three commits.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            |                                                                                                                                                                                                                                                                                                             |
| The missing session summaries                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               | Re-supply them. Several verdicts will change from UNVERIFIABLE, and the two cross-session equivalence questions become answerable.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 |                                                                                                                                                                                                                                                                                                             |
| §1.1.8 as a live override — **closed 2026-09-30 by Phase A, replaced by a different question**                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              | Nothing more to verify: `9b774f9` is the change, dated and published. What is open is whether a bound change belongs in a commit whose message does not name it. Recorded as a commit-hygiene finding, not a repo defect, and not fixed here.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      |                                                                                                                                                                                                                                                                                                             |
| §1.6.3 session 7's two remaining files                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      | `docs/adr/0003-consolidated-phase1-schema-expansion.md` (last touched `5c65597`, 2026-09-28 06:34) and `docs/UMAMUSUME_REFERENCE.md` (last touched `24e491c`, 2026-09-29 05:09) carry no commit in the window and no working-tree change. Either the edits were never written or they were reverted by a path that leaves no trace. This is a finding about session 7's own reporting reliability; it cannot be closed from the repository.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        |                                                                                                                                                                                                                                                                                                             |
| The stash at `7bbe4d5`                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      | Owned by nobody. Needs the human to say keep, pop or drop — now queued as §3.8, not verified here.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 |                                                                                                                                                                                                                                                                                                             |
| `git merge-tree --write-tree` residue                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       | Two merge simulations during Phase A wrote unreachable tree and blob objects into `.git/objects`; the loose count moved from 425 to 451. They are not refs and not history, and they will be pruned with everything else. Deliberately not cleaned, because cleaning means `gc` or `prune`, both fenced by §7.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     |                                                                                                                                                                                                                                                                                                             |
| Commit-message scoping: `afd3ae1` and `9300e6c` edit D-285's body while their messages name D-289                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           | §5.6's per-commit file lists settle §1.4.2's stronger half as **refuted**. `afd3ae1` touches only `docs/research-scratch/DESIGN-CORPUS.md`, but its changed lines carry D-285 eight times against D-289 once; `9300e6c` carries D-285 twice and no D-289, and its visible change is inside D-285's paragraph ("The corrected line 357…" becoming "The corrected §1.3.4 cap row…"). Only `a18d77a` is D-289-scoped, and even it applies the rule to `KNOWN-ISSUES.md` and a verification record rather than editing D-289's own text. All three are D-289-*motivated*; two land in another rule's body. Recorded as a finding about attribution legibility, not as a defect — no commit is amended and no rule is renamed.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             |                                                                                                                                                                                                                                                                                                             |
| Phase A measurement corrections — three, all in the direction of "the chain is bigger than reported"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        | (1) **Eight commits became ten**: `master..rescue/profiles-chain` counts 10, because `992016d`'s line sits on `8906a40` and `7cc6283`, which `feat/catalog-roster-and-trainee-selector` already protects. (2) **The chain does not fork from master**: `92670ff`'s parent is `7cc6283`, so it continues the roster branch; the master-side fork `8acb678` is an ancestor of that branch, not its base. (3) **`dd90330` nearly lost its citation**: a `git show --stat                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              | grep -c migrations` returned zero because git elides long paths as `...29_add_skill_lists...`, which reads as absence. The commit does carry the migration. A future reader taking "eight commits, forked from master" at face value would plan the wrong merge, so the correction is the deliverable here. |
| **A Laravel builder method that compiles, runs, and emits nothing** — `Blueprint`'s column `->check(...)` on SQLite (Slice 2, 2026-10-01)                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   | Nothing left to verify; the row records the class. `2026_09_30_142618` wrote four `->check(...)` calls, and the DDL it produced contains no `CHECK` token in any of the three tables. `Blueprint` has no table-level `check()` at all (`BadMethodCallException`), and as a column modifier the SQLite grammar discards it. The reported consequence was worse than a missing constraint: a review accepted the constraint as real, asked why a value slipped past it, accepted "SQLite does not enforce it" as the answer, and **deleted the test** that had surfaced the question. The scan for other sites is done and clean: `git grep -- "->check(" -- database/migrations app` matches only this one migration's four calls and the two files that discuss them. Same shape as the phrase-literal regex bugs and §6's `dd90330` elision row: a check that cannot fail, reporting as a check that passed. Fixed forward in `2026_09_30_151945`, which writes the constraints in raw SQL and verifies both the rejection and the layer that catches factories.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  |                                                                                                                                                                                                                                                                                                             |
| **`cp` of a WAL-mode SQLite file yields a file that is not the database** — the mechanism behind §6's first row (Slice 2, 2026-10-01)                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       | Now filed as **KI-44**, so this row points rather than re-states. Measured on a throwaway file: after one insert, main = 4,096 B and `-wal` = 1,751,032 B, and the copy of the main file alone does not even declare the table. Two symptoms, one cause: Slice 2's real copy read `database disk image is malformed`, the probe's reads `no such table`, and the quietest outcome between them is a valid-looking **empty** database. `config/database.php:42` declares `journal_mode => env('DB_JOURNAL_MODE', 'wal')` and line 41 `busy_timeout` 10000, both present since the initial skeleton commit `fda6ff0`, so WAL is intentional here rather than environmental — a peer report that "no setting in this repository enables WAL mode" is wrong at the config layer while being right about the effect. Closes the operational question, not the policy one: the rule is checkpoint-then-copy (`UmaBackup.php:32`) or migrate-a-new-file, and it applies to every session in this repo.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    |                                                                                                                                                                                                                                                                                                             |
| **The shared development database was rebuilt mid-pass, by a peer, without announcement** — escalated 2026-10-01, attribution now confirmed                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 | Not a verification gap; a coordination hazard with no control on it. Confirmed cause: a peer session stopped the dev server (PIDs 19272/18736) **in order to** run `migrate:fresh --seed` against `database/database.sqlite`, then reported the resulting file as its "current state". Fingerprint trace across the window: `1,667,072 @ 19:11` → `409,600 @ 22:51:41` → `1,179,648 @ 23:59:43` → `783,616 @ 00:30` with `-wal`/`-shm` present. Slice 2 was fingerprinting that same file while it changed, and inferred a rebuild from the size drop before the cause was reported; the inference was right, the framing "someone silently lost a fixture" was not. **Severity, corrected by measurement rather than by intent:** all three files in `storage/app/backups/` (09-29 15:30, 09-30 14:51, 09-30 15:52) hold `training_runs = 0` and `turn_entries = 0`, so no Trainer-authored run data ever lived in the shared database; what the rebuild discarded was regenerable reference data, which the peer's seed reproduces offline. **Qualification 2026-10-01, found while closing Slice 6 — the clause above is kept verbatim because its conclusion probably holds, but the evidence behind it does not.** No live count of `training_runs` was ever taken on the shared file; every number in that sentence is a count of the *backups*. A read-only `SELECT COUNT(*)` on the live file at the close of Slice 6 returns **4 rows**, and the shared database has therefore held run rows continuously since 2026-09-30 16:54. They are ids 1–4, all `umamusume_id = 9`, one per scenario (`NULL`, `ura_finale`, `unity_cup`, `trackblazer`), all `Active`, `notes = ''`, created 16:54:48 → 16:56:12 at ~30-second intervals with `updated_at` equal to `created_at`, and `turn_entries = 0` alongside them. That shape is a per-scenario verification pass, not a Trainer's career, so nothing valuable was almost certainly lost — but "nothing valuable was lost" is now a judgement about the data's character, not a measurement, and a future `migrate:fresh` destroys four rows somebody made plus whatever a real run adds later. **The durable rule this leaves behind: count the live file, not only the backups, before stating what a rebuild could cost.** Same read also records that the live shared file today has `support_cards = 0` and `race_catalog_slots = 0` — the second is KI-45's blocker, still a blocker — and carries **no `imported_at` column**, so Slice 4's migration has never been applied there. Fingerprint state at that moment: main `1,179,648 @ 00:56:12` → `1,310,720 @ 02:41:21`, a third write window inside this session's working period; all three mtimes (main 02:41:21, `-wal` 03:03:21, `-shm` 02:43:39) precede this session's first read of the file at ~03:38, so the Slice 5 and Slice 6 commands wrote nothing there, and none of these timestamps is a self-inflicted artifact of the check that found them. What remains unowned is the pattern: shared state changed under a running measurement, no fence prevented it, and no announcement preceded it. This is the hazard this document's pre-existing `git status` row describes from the other side — that row says a read of the index cannot be taken as a snapshot while sessions are live; this one says a read of the shared database cannot either. Neither had a standing rule behind it before this row, and §4 now queues one. Restoration is **not** attempted and is the owner's call; the 15:52 backup is the newest snapshot, not a pre-rebuild one, and its filename is UTC while its mtime is local. **Added by the owner on 2026-10-01, after the Slice 4 report:** the test suite is currently isolated from the shared file only by an **uncommitted** change — `phpunit.xml`'s `DB_DATABASE` is `:memory:` in the working tree and `testing` on every ref (`git show HEAD:phpunit.xml` line 26, and `git log --all -S 'value=":memory:"' -- phpunit.xml` returns nothing). `.env.testing` also says `:memory:`, and it is gitignored (`.gitignore:22` `.env.*`), while PHPUnit's injected `<env>` values win over it anyway — which is why the file-based value was silently winning. So a fresh clone today still creates `testing`, `testing-wal` and `testing-shm` in the repository root, and the isolation every session this period relied on is a local condition rather than a property of the project. Same shape as KI-24b (green in the developer's tree, absent from a fresh checkout), though a different trap: KI-24b was a gitignored skill registry, this is an uncommitted config correction. The mechanism is KI-42's — nothing runs the gates on a fresh checkout, so a defect visible only there is found by whoever remembers to look. **Action: the `:memory:` fix must be committed by whoever holds it; until then it is not a control, it is a coincidence of this tree.** |                                                                                                                                                                                                                                                                                                             |
| **SHA fingerprinting of `database/database.sqlite` is not always available under concurrent access.** The skills-section review could not read a SHA-256 of the shared database before or after its browser pass, because another process held the file open. Size and mtime were available and stable (`1310720`, `2026-09-30T18:41:21.7903368Z`), and the no-change statement rests on those. This is the same class as the `git status`-not-a-stable-read row: the definitive check is unavailable under contention, and a record that falls back to a weaker check must state which check it used. Do not report a SHA-based no-change claim when the SHA could not be read.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            | Rule, not a gap: a shared-file fingerprint is taken as size plus `LastWriteTimeUtc`, and any report resting on those two states which check it used and why the content hash was unavailable. Reading a hash through a copy is not the fix, for the reason §6's `cp` row records: a copied WAL-mode SQLite main file is not the database.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          |                                                                                                                                                                                                                                                                                                             |
| **`vendor/bin/paratest` cannot run this suite, and exits 0 anyway** (found 2026-10-01, Slice 2 gate 5)                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      | ParaTest 7.20.0 on PHPUnit 12.5.33 prints `Please run [./vendor/bin/pest] instead.` and exits **0 having executed no tests** — so any gate list or CI job that adds paratest passes silently. Confirmed twice, including with the correct flag (`-p, --processes`, which `-p` documents; the first refusal was not a flag error). Pest 4's custom runner is the cause, not configuration. **Do not add it to any gate.** Where a workflow already contemplates it, KI-42's candidate CI job is the case in point, the workflow must be read before it lands. Substitute for the cross-process isolation question: `vendor/bin/pest --order-by=random --random-order-seed=4321`, which on 2026-10-01 returned the same 972 passed / 2 skipped / 16,232 assertions as the sequential run. Related and worse: a `PRAGMA`-style probe that reads a key the document does not have returns a benign-looking zero rather than an error — this row and the `characters.json` mis-measurement in `ADR-0014` are the same failure shape as §1.4.2's elided-path zero.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       |                                                                                                                                                                                                                                                                                                             |
| Phrase-literal searches undercount claims that have variants                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                | §1.4.3 concluded "two sites" by searching the strings `twelve columns` and `12 columns`. The concept — how many fillable columns `character_cards` has — is stated in at least six places, in four different phrasings: `ADR-0008:235` "fillable over twelve columns", `ADR-0008:438` "twelve fillable columns in all", `ADR-0012:7` "carries the twelve columns", `ADR-0012:73` "alongside the existing twelve", and two in `docs/requests/2026-09-29-catalog-roster-and-trainee-selector-plan.md`. It is also stated *without any number at all*: `ARCHITECTURE-ESSENTIALS.md:30` enumerates twelve column names and `ARCHITECTURE.md` §3 lists the table, and neither mentions `skills_innate` or `skills_unique` (`grep -c` returns 0 for both files). Future scans must search for the concept, not the string. Same failure shape as the eleven-of-thirty refutation rate in §1.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             |                                                                                                                                                                                                                                                                                                             |
| Plan documents carry point-in-time claims that must not be retroactively corrected — but check whether the plan is still open first                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         | `docs/requests/2026-09-29-catalog-roster-and-trainee-selector-plan.md:423` says "Amendment A1 widened the table to twelve fillable columns". That is a claim about what A1 *did*, it was true when written, and rewriting it to fourteen would falsify the history. It stays. The same file at `:839` is a different case: it sits under "What must end up true, in both documents" in **Step 13**, whose checkbox is unchecked — the plan measures 116 unchecked and 0 checked — so it is an open task's acceptance criterion, not a record. Executed as written it would re-inscribe the twelve-column list into both design docs. **Authorized and corrected 2026-09-30 in `d7ae84c`:** the criterion now reads twelve as drafted, fourteen as it stands, pointing at the live `#[Fillable]` list, and a note sits directly under Step 13's heading so the instruction cannot be executed without it. `:423` was left untouched for exactly the reason above.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   |                                                                                                                                                                                                                                                                                                             |
| Plan documents' acceptance criteria are executable instructions, not prose                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  | The `:839`-versus-`:423` distinction — one historical, one live — surfaced **only** because the checkbox state was checked. Read as prose, both sentences assert the same twelve-column count and both look equally correct or equally stale. A future scan for stale content must classify every plan-document claim by whether the step that owns it is open or closed: an unchecked step makes the claim a *live hazard* (something will execute against it), a checked or completed one makes it *history* (correcting it falsifies the record). This is a different axis from the phrase-literal lesson above — that one is about *finding* claims with variant wording, this one is about *grading* the claim you found by the executable state around it.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   |                                                                                                                                                                                                                                                                                                             |
| Design-doc drift window: two files carry the stale twelve-name list today, deliberately                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     | As of 2026-09-30, `ARCHITECTURE.md` §3 (the `character_cards` block and the Catalog-fence paragraph) and `ARCHITECTURE-ESSENTIALS.md:30` (the digest line) still enumerate twelve column names and omit `skills_innate` and `skills_unique`; `grep -c` for either column returns 0 in both files, while `#[Fillable]` in `app/Models/CharacterCard.php` lists fourteen. They are **not** pre-corrected, and that is the decision, not an oversight: reconciling them is precisely Step 13's assigned work, so editing them here would strip the step its purpose and create a second account of "the current list" — the fragmentation this whole pass exists to reduce. The window is bounded and closes the moment the plan owner fires Step 13; the corrected criterion and the in-step note now make it fire correctly. The live count during the window is fourteen; the count in those two documents is twelve; the authoritative reader is `#[Fillable]`.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   |                                                                                                                                                                                                                                                                                                             |
| **Shared docs are edited by multiple sessions with no coordination.** `SESSION-CONSOLIDATION-2026-09-30.md` moved at HEAD twice while a single session was appending to it — a peer commit at `c1e14a3`, and a mid-pass HEAD shift before it. The document is a hub node (cited by every downstream decision) and it has no merge discipline. Until one is chosen, any agent appending to it should commit immediately rather than leave edits dirty for a sweep.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           | Recorded as an observation; the merge discipline itself is a human call and is not proposed here. A full-tree sweep that finished after §8 was written also located `ura-finals` in three ignored dataset exports (`.scratch-uma/cited/scenarios.json`, `.scratch-uma/cited/static_scenarios.json`, `research-scratch/data/json/scenarios.json`), always as the `url_name` field of scenario id 1 — the upstream slug, not an application key. The same pass also produced a coordination miss worth recording beside the pattern: this session reported `rescue/profiles-chain` (`8ffab63`) as peer work landing mid-turn, when the ref already existed from an earlier authorization. That was a stale view, not a collision. Consequence for any session on shared state: re-read refs and `HEAD` immediately before reporting, because absence in your own view is not evidence that another session just created it. **Escalate to owner**, ruled 2026-09-30 on the fifth-or-sixth instance across sessions: the deprecated-PDF review pass measured 12 peer commits on `master` between its session-start `89675e6` and `6227417` while a single read-only pass ran, all under the same git identity (`IzzatFirdaus`) in this shared worktree, with no fence breached and none of that pass's untracked work swept into a peer commit. The pattern is not a code problem and no agent can settle it. It needs a human policy choice among worktree isolation, identity separation, or a cross-session commit discipline, because every line-number citation a session writes pins to a `HEAD` that has moved by the time the session ends.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               |                                                                                                                                                                                                                                                                                                             |
| **A line-number correction is itself a line-number citation, and it rots on the same clock.** This is now the third time the pattern has repeated, and each time the correction was written by a pass that had just criticized the original for being a line number. (1) `docs/design-research/DESIGN.md:191` cites `KNOWN-ISSUES.md:855-861` for four ratios KI-20 records; those lines are inside KI-15, and KI-20's heading is `## KI-20 The shop error text has never been measured as a rendered pair`. (2) Row 1.4.1 above refuted the range and got the entry wrong as well, attributing it to KI-10; see its forward note. (3) `slice-7-2026-09-28.md` cites a Blade comment at `layout.blade.php:62` that now sits at `:66`, and `slice-10-2026-09-29.md` lists three detector false positives of which only `stat-band.blade.php:123` still resolves at its line. The rule is not "correct stale line citations", because the correction is stale by the next commit. The rule is: **in register files, ADRs and slice records, cite the heading and never the line.** `KNOWN-ISSUES.md` gives every entry a heading, `docs/adr/*.md` every decision, and the slice records their own `## N.` sections, so the durable anchor already exists in all three classes. Where a heading genuinely does not exist, cite the rule ID (`D-289`, `KI-15`, `R57`) instead, which is what D-289's own fix at `a18d77a` ("cite by heading") was for.                                                                                                                                                                                          | Nothing to verify. This is a convention decision for the human, and adopting it is a Docs Writer change to how citations are written, not a repository defect. §7.11 already warns that every line number in this file dies at the next commit; this row names the mechanism and the replacement.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  |                                                                                                                                                                                                                                                                                                             |
| **`git status` in this worktree is not a stable read, so a clean status is not evidence that nobody else is writing.** Observed directly during the documentation inventory at `c1e14a3`: one call returned `M  docs/design-research/SESSION-CONSOLIDATION-2026-09-30.md` as a staged change, and the call immediately after it returned nothing, with `git diff --cached --stat` empty on both sides. No session in that window reported staging it, and the inventory session made no writes to any tracked file. That is the same shape as §7.10 and §7.11 seen from the other side: those say do not run destructive git here while sessions are live, and this says do not *read* git here as a snapshot either. Index state can belong to a concurrent session and change between two of your own commands.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           | Needs a standing rule the human chooses: either the whole worktree is treated as eventually-consistent, where every read of `HEAD`, `git status` or an index is re-taken immediately before the action that depends on it; or concurrent sessions get separate worktrees, which is what §7.11's "commit immediately rather than leave edits dirty for a sweep" is working around. Not proposed further here.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       |                                                                                                                                                                                                                                                                                                             |
| **A check whose positive and negative cases return the same value.** Such a check cannot fail. It reports success while proving nothing, and every one of them found so far produced a confident wrong answer rather than an error. Instances verified in this pass, with the command that reproduced each. (1) `Blueprint::check(...)` on SQLite: `grep -c "public function check" vendor/laravel/framework/src/Illuminate/Database/Schema/Blueprint.php` returns 0, so there is no table-level method, and `database/migrations/2026_09_30_142618_create_support_cards_and_support_effects_tables.php:10-12` records that the four `->check(...)` calls in it render no SQL at all. (2) `git rev-list X --not --all --count` returns 0 for `master`, for `feat/catalog-detail-page`, for the unreferenced `992016d`, and for `archive/profiles-chain`, because `--all` already contains the ref being measured. The same ref through the exclusion form returns 8. (3) `->check()` as a column modifier, distinct from (1): the table-level method does not exist, the column modifier does exist and the SQLite grammar drops it, so the failure looks like a passed migration in both cases but the cause differs. (4) Phrase-literal searches that miss concept variants: the twelve-column question was first reported as two sites, while a literal `twelve column                                                                                                                                                                                                                                                                   | 12 column` search today matches four tracked files, and the concept also appears with no number at all. (5) Line-scoped searches that miss hard-wrapped prose: in `docs/adr/README.md` the six-word run "by appending a dated erratum that" returns 0 line-scoped and 1 with lines joined. (6) The rescue verification itself: the zero from (2) was read as "nothing at risk" when the correct reading is the opposite, that 8 commits are unique to the ref. One proposed instance did **not** reproduce and is dropped rather than listed: the `File::mimeType()` setter-versus-getter case has no trace in tracked content, `git grep -ic mimetype` matches only `composer.lock`, so it cannot be cited here. Two remedies follow, and they are uniform: pair every check that returns zero with a canary that proves the pattern can fire on a known positive, and verify every "nothing at risk" claim with the exclusion form rather than the inclusion form. Separate observation, not established here: a scan for a`CHECK` token in executable SQL across every migration in `database/migrations/` found none, which suggests the raw-SQL rebuild that `2026_09_30_151945_correct_support_card_schema_and_constraints.php:12-15` describes may not have landed either. That claim needs its own check by someone who can run the schema; the pattern used was `add constraint                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           | check *(...)` and it is crude. Filed as a discipline note for gate design, not as a KI, because no shipped behaviour is broken by the pattern itself.                                                                                                                                                       |
| **Erratum on the row above, same day, so the speculation it carries is not left standing.** The incidental claim that no `CHECK` token was found in any migration, and that the raw-SQL rebuild in `2026_09_30_151945` may therefore not have landed, is **refuted**. The test was the definitive one rather than another source grep: `DB_CONNECTION=sqlite DB_DATABASE=<fresh path in temp> php artisan migrate --force` built a 307,200-byte scratch database, and a read-only query of `sqlite_master` returns `check ("rarity" in (1, 2, 3))` and `check ("type" in ('speed','stamina','power','guts','intelligence','friend','group'))` inside the `support_cards` DDL. `CHECK` appears in the DDL of five tables in that database: `support_cards`, `support_effects`, `deck_slots`, `scenario_slots` and `race_catalog_slots`. Canary proving the read can fire: 40 of the 56 tables carry some constraint keyword, so an absent CHECK would have been visible rather than silently empty. **The mechanism of the false zero is the class named in the row above.** The scan grepped the uppercase literal `CHECK` against PHP source, while the rebuilt DDL emits lowercase `check`, so the pattern could not match the thing it was searching for. The dispatch method list already states the rule, case-insensitive searches when the target is prose, and the miss shows the rule applies to generated SQL too. Consequence: no KI was filed for that item, and the migration's own account of what it did is correct. The shared database was untouched, its three file digests identical before and after the scratch build. |                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    |                                                                                                                                                                                                                                                                                                             |
| **Second-order erratum, 2026-10-01, correcting the row above and a dispatch that relied on it.** The erratum's mechanism claim, that the first false zero came from grepping uppercase `CHECK` while the DDL writes lowercase `check`, is only half right. Two scans failed for two reasons. The first was case. The second used a case-insensitive pattern of the shape `check *\(*[a-z_]+ +(in                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            | between)`, and the live text is`check ("calc" in (...))`, so the double quote between the parenthesis and the identifier broke the match before the keyword was ever reached. The durable lesson is narrower and stronger than case sensitivity: a constraint that Laravel emits at runtime cannot be found by any pattern over PHP source, in either case, and only a read of built DDL answers it. Also refuted, from the same pass, are two claims a follow-up dispatch made about`2026_09_30_151945`. First, that its current text calls`$table->check(...)` in a two-argument array form. A scan of every executable line in that file finds no `->check(` call at all; only `2026_09_30_142618` uses that method, four times. `151945` writes its constraints as raw SQL inside three private rebuilders, `rebuildSupportEffects()` at `:188`,`rebuildSupportCards()` at `:216` and `rebuildDeckSlots()` at `:262`, each emitting`check (...)` in a `CREATE TABLE` heredoc. Second, that KI-50 therefore needed a forward note because it claimed the constraints were verified against the post-rollback schema. KI-50 makes no such claim. Its actual sentence, that `151945`'s`down()` deliberately reconstructs the constraint-free shape, checks out: `down()` begins at `:71` and the only `check` tokens in the file sit at `:196`,`:227`,`:229` and `:269`, all within`up()`'s rebuilders, so a rollback really does produce the parent's constraint-free state. The note was correctly withheld, and the premise for filing it was wrong. **Vendor mechanism, measured rather than quoted.**`Blueprint.php` has 0 lines containing `check` in its 2,036, and it defines no `__call`.`SQLiteGrammar.php` has exactly 1 line containing `check`, line 876, which is the return value of`typeEnum()`, so the only way the framework writes a CHECK is through an`enum` column. `Fluent::__call` at `Illuminate/Support/Fluent.php:130` stores an unknown method name as an attribute, which is what makes `142618`'s`->check(...)` a silent no-op: the attribute is set, no grammar modifier reads it, and nothing throws. That confirms instances (1) and (3) above on mechanism, while making KI-50's heading wording, `Blueprint::check()`, imprecise: the call lands on the column's Fluent object, not on`Blueprint`. **Ninth instance of the class, and the one that applies to this document.** A forward correction that names the wrong mechanism is itself a check whose positive case never ran: it reads as a fix, gets believed because it is dated and appended, and propagates. The dispatch that supplied both wrong mechanisms was itself the clean-room reader of the row above, and nothing in that row could have caught it. So a forward note needs the same canary discipline as an original claim, and a correction should name the command that re-tested it, not only the conclusion. **One more shared-state finding.** The scratch database used for the C-5 read lives at a temp path that is not private. Its mtime moved to 05:21 after this session built it, and its`migrations` table now holds 2 rows where this session's build recorded the full set, so a peer rebuilt it in place. Any agent that re-runs a scratch check against a fixed temp filename may read another session's database and should use a path unique to the run.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             |                                                                                                                                                                                                                                                                                                             |
| **Scratch paths are not private, and the collision is easy to miss.**                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       | A scratch database written to a fixed temp filename was overwritten by a concurrent session, leaving an earlier build in place without notice. Nothing reported an error. A later read of the same path returned a different migration set than the one this session wrote, and only a size plus mtime check exposed it. The shared `database/database.sqlite` is the escalated case, with a named hazard and a recorded SHA discipline. This is the ordinary case, same class, much smaller blast radius, and it is the one an agent will hit routinely while verifying DDL. **Rule.** Any scratch database whose state must survive more than one command takes a run-unique name, `skreview-<pid>-<timestamp>.sqlite`. Never reuse a fixed temp path for a scratch build, and never report a scratch result without the file's size and mtime, because those two fields are what reveal the overwrite. Recorded here rather than only in the second-order erratum above, because the erratum names the incident and this row names the discipline.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              |                                                                                                                                                                                                                                                                                                             |

| **A scratch server is not private.** | The UI/UX pass served its run-unique scratch database on port 8741; a peer session reached that port and a run was deleted from the scratch database. Observed: the scratch file went from two runs and three deck slots to `runs = 0` and `slots = 0` while `cards = 559` and `effects = 35` survived, and the server log carries requests this pass never navigated, `/umamusume/architecto-quaerat`, `/_boost/browser-logs`, and two `GET /training-runs` hits at 23:20:35 and 23:20:45. Inference, not observation: the run-delete form is named as the mechanism because it is the only app path that clears runs while leaving the catalogue whole, and `artisan serve` logs no request method, so the log cannot confirm a `DELETE`. The port was also picked by convention rather than at random, twice (8741, then 8975 for the after-measurement). **Rule.** A throwaway port is a shared resource too. Bind to `127.0.0.1` and use a port selected at random rather than by convention, or close the server between measurement passes rather than leaving it up while other work proceeds. Isolated scratch databases do not protect against a peer scanning or colliding on local ports. Recorded beside the `Scratch paths are not private` row above: same class one layer up, the socket instead of the file. **A scratch server is not private, addendum 2026-10-02.** The path `/umamusume/architecto-quaerat` in the citation above is verified against `routes/web.php:14` (generic `/umamusume/{slug}` route) and against line 94 of the server log; the row is a factory-generated trainee in the scratch database, not a game character, and its slug resolves through the generic route. The finding is not the path: it is that requests appeared in the log which this pass did not make, and that a run was deleted from the scratch database while the pass was measuring it. Separately, the shared database was cleared and re-seeded during the same window; the peer is the Crush IDE agent per the owner's report, and this pass's own fingerprint pair (main `1,716,224 B` at `00:16:30.490`, `-wal` present at `0 B` then absent at `00:17:01.543`) is the evidence that a second writer was attached. |

| **A correction is a claim and carries the same burden of proof as the original.** | Before a reviewer's correction lands, verify its premises against primary sources (the route table, the factory, the log file's own bytes), not against the reviewer's framing of them. Filed 2026-10-02 after a dispatch ordered the withdrawal of a citation that was real: `routes/web.php:14` defines the generic `/umamusume/{slug}` route, `UmamusumeFactory.php:24` builds factory names from faker's Latin words, and line 94 of the scratch server's own log holds the string the dispatch called fabricated. The premise behind the withdrawal, that no Umamusume carries that name, was true of the game and false of the scratch database the log was reading. The same dispatch also instructed an amendment to a row that does not exist in this file, and an ordinal with nothing to count. Twice in one session a correction arrived naming a mechanism its author had not tested, and the executing agent's check is what kept the wrong one out of this record. This is the rule the `Scratch paths are not private` and `A scratch server is not private` rows above sit beside, and it is the same lesson the second-order erratum row already states for forward corrections: a dated correction that names the wrong mechanism reads as a fix, gets believed because it is dated, and propagates. **Rule.** A correction is a claim. Test it against the primary source before it lands, and when it fails, say so rather than filing it. |

| **One git index serves one worktree, so streams that share a worktree cannot commit concurrently.** The 2026-10-01 parallel dispatch planned six streams with disjoint file ownership. The file disjointness held, and the streams still could not commit concurrently: all six ran inside this one worktree, which has one index, so two agents staging in the same window can place each other's paths into a commit that then carries both. The follow-up dispatch's drafted row described the mechanism as "the git index is shared across all worktrees in one repository", which is wrong, and the row corrects it rather than landing it. `git worktree list` on 2026-10-01 shows three worktrees (the main tree at `master`, a temp tree on `feat/catalog-detail-page`, and `.kilo/worktrees/cypress-cardamom` detached), and each linked worktree carries its own index at `.git/worktrees/<name>/index`. Objects and refs are shared across them; the index is not. That distinction is what makes the drafted remedy ("or use per-stream worktrees") work, and the drafted reason would have made it look like it should not. | **Rule.** Parallel work inside one worktree is fine; concurrent commits in it are not. Serialize the commit step, or give each stream its own worktree, which is safe precisely because the index is per-worktree. File ownership disjointness is necessary and not sufficient. The dispatch that withheld parallelism made the correct call for a reason it stated slightly wrong, and a protocol rule with a false mechanism in it is a rule the next agent tests and discards rather than follows. Recorded beside the `Scratch paths are not private` row above, since both are about state one session assumes it owns. |

| **A skill-availability check run as a filesystem scan reports absence over installed files.** The 2026-10-01 session reported `browser-testing-with-devtools`, `code-review-and-quality` and `source-driven-development` as not found anywhere after searching six skill roots. All three are installed at `~/.qoder/skills/<name>` and symlinked into `~/.agents/skills/<name>`, and `find ... -type d` does not match a symlink to a directory, so the scan returned nothing for entries that were present. The verdict that same session reached, environment gap rather than registry gap, was right and came from a different route: the Skill tool in that session, and again in the one after it, returns `Skill "<name>" not found`. So the correct conclusion was reached on false evidence, which is the worst version of this, because the false evidence would also have supported the opposite conclusion. | **Rule.** The durable test for skill availability is the Skill tool's return value, not a filesystem scan. Attempt the invocation and report what the tool says. Where a scan is used anyway, use `ls -la` or `find -L` so a symlink is visible, and pair every zero with a canary that proves the pattern fires on a known present entry. This is another instance in this file of a search reporting absence over something present, beside instance (5) of the cannot-fail-check row, where a six-word run returns 0 line-scoped and 1 once the lines are joined, and the `CHECK`-token erratum, where scanning PHP source found none while the built DDL emits `check (...)`. The class is identical in all three: the instrument's shape produced the zero, not the target's absence. |
| **A register entry can go stale against shipped code and no guard would catch it, because the doc-drift guard exempts the register by design.** `555b0cb` (2026-09-30 03:44) shipped `resources/views/components/aptitude-grid.blade.php`, which renders all ten aptitude letters, and wired it at `resources/views/catalog/partials/form-detail.blade.php:56`. That falsified KI-35's Class A claim that `grep -rn aptitude resources/views/` returns zero hits, and the entry had been filed the day before, so the claim aged about one night rather than being wrong when written. Nothing in the suite could have caught it. `DocSchemaDriftTest` exists to stop a governance doc describing an applied change as un-landed, and its own comment at `tests/Feature/DocSchemaDriftTest.php:24-26` names `KNOWN-ISSUES.md` exempt on the reasoning that the register records history and plans later work, where asserting against it would be retro-asserting the past. | **Rule.** The exemption is correct and stays. Its consequence is that no automated guard reads the register, so a measurement quoted from a register entry is a claim from a dated snapshot and gets re-run before it steers a recommendation. That is `CONSTRAINTS.md` D-289 applied to a number rather than to an anchor. The instance that produced this row is the cheapest demonstration available: a follow-up dispatch drafted an erratum asserting the grep still returns zero hits, and the grep returned three files. Recorded beside the `One git index serves one worktree` row above, since both are state a session assumed it could read as current. Not enforceable by a gate without deleting the exemption that protects the register's history, so it is a discipline, and the discipline is the re-run. |
| **Audit finding IDs are not namespaced and collide across audits.** `4e17997` is titled for "F-1, F-2" against a UI audit's labels; the batch-atomicity audit uses the same IDs for two unrelated Critical findings. `app/Http/Controllers/TrainingRunController.php:616` cites "(audit F-7)" against the preview-405 finding while the code dispatch's F-7 names the atomicity finding. A verifier searching history for "F-1" or "F-7" gets a commit or a code comment claiming a fix that has nothing to do with the finding. **Rule: audit finding IDs carry an audit prefix** (`UI-F-1`, `PIPE-F-1`) **or they are promoted to KI numbers before they reach a commit message or a code comment.** Do not rewrite the landed commits or the code comment; the convention applies forward. `KI-` numbers are already unique across the repository, which is why they can be cited across audits. | Add the prefix rule to `CONSTRAINTS.md` and enforce in review. Existing IDs grandfathered; new findings must namespace or promote. |
| **Third state change on `database/database.sqlite`, 2026-10-01 18:04 to 19:54.** Before: main = 1,310,720 B @ 18:04:31, `-wal` = 12,392 B @ 18:11:59, `-shm` = 32,768 B @ 18:11:59. After: main = 0 B @ 19:54:19, `-wal` and `-shm` byte- and time-identical to the before state. The WAL and SHM modification times are frozen at 18:11:59, before the 19:54 cutoff, so no peer process is currently writing. The main file was truncated from 1,310,720 bytes to zero while its WAL retained 12,392 bytes of uncommitted frames, which is **consistent with** `migrate:fresh` destroying the database without checkpointing the WAL first. The mechanism is inferred from the fingerprint; no peer commit or process log names it. Fingerprint sequence: 09-30 19:11 = 1,667,072; 09-30 22:51 = 409,600; 09-30 23:59 = 1,179,648; 10-01 02:41 = 1,310,720; 10-01 19:54 = 0. git status at 12:17 shows `config/uma.php`, `phpunit.xml`, `DatabaseSeeder.php`, and `UmamusumeSeeder.php` dirty from a peer, consistent with a peer session that was mid-edit when this state was captured. No checkpointed backup was created after 18:04 (the three files in `storage/app/backups/` date to 09-29 23:30, 09-30 22:51, and 09-30 23:52, all before the change window). The pattern repeats: a peer drops the shared database mid-session without announcement, a third time. The peer's own session state was destroyed by the same operation; the shared database is unsafe for the session that touches it, not only for observers. Two commits from a parallel code dispatch (`fb1387b`, `48a0402`) landed in the same window. The tree is being written by more than one session during this audit. | Escalate to owner: the worktree-isolation policy proposed in §3.6 is now blocking real measurement, not merely inconvenient. Policy options for the owner: (1) per-session scratch databases with a naming rule; (2) per-session worktrees, one DB each; (3) a hard rule against `migrate:fresh` on the shared file, enforced or documented; (4) stop treating the shared database as fixture -- every session uses `:memory:` for tests and scratch for browser work, and the shared file is for ad-hoc queries only. Option 4 is the smallest change that matches the session's current behaviour. **Correction 2026-10-01: the 307,200 B state was the fix dispatch's own `migrate` run without `DB_DATABASE` set, not a peer. The instance count stands; the attribution changes from peer to agent. The escalation is now an enforcement problem, not only a coordination problem.** |
| **Another fingerprint event landed after §9's fence correction, so the shared file is still moving under readers.** §9 corrected the 307,200 B @ 20:45:48 growth to the fix dispatch's own `migrate`, which ran without `DB_DATABASE` and so pointed at the shared file, not at a scratch path. Measured at 13:44 UTC, after that correction commit `026a5ae` (21:41:13 local) landed: main = 438,272 B @ 21:32:17, `-wal` re-created at 12,392 B @ 21:32:37, `-shm` at 32,768 B @ 21:32:37. Both sidecars sit at the exact sizes this section's first row documents as WAL mode's normal steady state, and `config/database.php:42` declares `journal_mode => env('DB_JOURNAL_MODE', 'wal')` explicitly, so the earlier claim that no setting in this repository enables WAL mode is wrong at the config layer. The live four-file check at landing time still shows `config/uma.php`, `phpunit.xml`, `DatabaseSeeder.php`, and `UmamusumeSeeder.php` as ` M `, with `database/seeders/data/race-tier-labels-2026-09-29.json` still ` D ` and its `.held-aside` sibling still untracked. Attribution for this change: no commit between `026a5ae` and the measurement names a migrate, and a live `php.exe` (PID 9060) plus a `devsense.php.ls.exe` were present at measurement, so the write is an in-progress session against the shared file, consistent with the pattern in the row above; the specific command is not observable without opening the database, which the fence forbids. Rule for every session that reports on this file: take the fingerprint (main size and mtime, `-wal` size and mtime) in the same command batch as the read you are certifying, because the state at report time may not be the state at commit time; where attribution is impossible, write "unattributed" rather than "peer checkpoint." | A fingerprint is only evidence about the instant it was taken. The owner's §3.6 policy pick is what closes this class of event for good. |

### 7. Do not act

Each item is destructive, irreversible, or touches shared state. None was performed. None may be performed without explicit human authorization naming it.

1. **Any recovery or repair of `database/database.sqlite`.** The 0-byte main file with a 1.67 MB WAL looks alarming and is a normal hot-journal state, not established corruption. Any "fix" attempt — `.recover`, a checkpoint, a reopen that triggers WAL recovery — rewrites the only copy of every committed row. Current exposure is total: losing the WAL loses the database.
2. **`migrate:fresh --seed`, `db:wipe`, or `migrate:rollback`.** All destroy the working database or its history, and the sessions proposing them cannot know what the WAL holds, because nothing observable can read it.
3. **Pushing, or pushing another thread's commits.** `master` is ahead of `origin/master` with nothing behind, so a push publishes every local commit, including those authored partly by other sessions; that is shared state and not reversible in the way that matters.
4. **Any write to `.gitignore`, including the §5.2 fix, without a plan for its encoding.** The file is classified binary by git because its last line is UTF-16LE. Editing it in place risks propagating that encoding, and its history is already invisible: `git show --stat` on the commit that changed it reports "Binary files differ", so no future blame is available. A clean rewrite to pure ASCII fixes three things at once and should be chosen deliberately rather than as a side effect.
5. **Merging the profiles branches in either direction.** No branch carries the second migration, so a merge is not currently possible; and the underlying schema question (§3.1) is unanswered, so a merge that became possible would hard-code an unruled choice. The hazard the branch's own ADR describes is real and its stated precondition cannot be met, since the canonical branch it names does not exist.
6. **Deleting `992016d`'s only copy, or letting it expire silently.** It is already unreferenced. It cannot be protected by an agent without deciding the work is wanted (§3.2).
7. **Deleting `D:\smoke.sqlite`.** It is a 1.4 MB SQLite database whose 72 tables (`loan_applications`, `helpdesk_tickets`, `support_tickets`, `pulse_*`, `roles`, `permissions`) belong to `D:/Projects/HRMS-BPM-ICT`, with zero Umamusume content. It is not this repository's file and no claim in this session authorizes removing another project's database.
8. **Deleting `D:\tmp\contrast.mjs`, `fresh2.sqlite`, `get_routes.txt`, or `git_status_docs.txt`.** All predate the repository's first commit and none is reproducible from it.
9. **Deleting the unmerged branches `feat/catalog-detail-page` and `feat/catalog-roster-and-trainee-selector`.** Each carries commits found nowhere else, and one carries a written instruction against merging it.
10. **Any `git checkout`, `switch`, `reset`, or `stash` in this tree while eight sessions are live.** `master` advanced three times during this one working period and `KNOWN-ISSUES.md` anchors moved twice underneath the citations in this document.
11. **Trusting any line number in this file after the next commit.** Every citation is pinned to `89675e6`. This warning extends past `git checkout`/`reset` to ordinary file-level concurrent edits on shared documents: several sessions append to this file, and to `KNOWN-ISSUES.md`, `CONSTRAINTS.md`, `DESIGN.md` and `ARCHITECTURE*.md` with no coordination, so an uncommitted edit on a hub document is the loss path, not the exception. See the §6 row on merge discipline.

### 8. Forward correction, appended at `c1e14a3` (2026-09-30 18:49:50)

Section 1.6.4 is upheld, and one method error in this document's own making is recorded here rather than quietly fixed.

**The verdict stands.** No tracked file contains the hyphenated scenario key `ura-finals` at `master` `89675e6`, and none contains it at `origin/master` `2033434` either (`git grep -l "ura-finals" 2033434` → 0 files). The underscore form is the only spelling in tracked content. A later check of `HEAD` returned one file, and that file is this document, matching the claim text quoted at §1.6.4.

**The error, stated exactly.** An intermediate verification pass printed two numbers and one file list. The list was "files containing *either* spelling"; the count of four was for the hyphenated form alone. Reading the list as though it belonged to the count produced a false appearance that four files in `.kilo/worktrees/cypress-cardamom/` carried `ura-finals`. They did not. That worktree is detached at `2033434` with a clean status, and `2033434` contains no such string. The earlier REFUTED verdict was reached by `git grep` on tracked content and was never dependent on the misread list.

**Scope limit, unchanged.** The hyphenated form was swept across tracked files at both revisions. It was not exhaustively swept across every ignored and untracked surface, because full-tree recursive searches exceeded their time budget on this machine. The claim under test was about config, code, docs and migrations, and that surface is covered.

**Scope limit lifted after this section was written; the paragraph above stands as of its authoring.** A full-tree sweep that had been moved to the background finished later and did cover the ignored and untracked surfaces. It found the hyphenated form in three files: `.scratch-uma/cited/scenarios.json`, `.scratch-uma/cited/static_scenarios.json`, and `research-scratch/data/json/scenarios.json`. In every case the occurrence is the `url_name` field of scenario id 1 — the upstream GameTora slug for the URA scenario — and never a scenario key in code, config, a migration or a document. All three files are ignored (`.gitignore:88` and `:87`), so none is tracked content.

**Effect on the verdict: none, and the claim now has a mechanism.** §1.6.4 stays REFUTED. The rejected claim was that two spellings of the application scenario key coexist. They do not: `ura_finale` is the only key in tracked content, across 60 files. The hyphenated string that prompted the claim is real but lives in a different namespace — an upstream field this application does not use as a key. That is the likely origin of the report, and it is a naming-coincidence, not a split.

**Provenance of this file.** It was written during Phase 2 and left untracked, per the no-commit fence. It is now committed, at `c1e14a3` ("docs(consolidation): acceptance criteria are executable, and the design-doc drift window"), by a session other than this one. Consequence: every line citation in section 1 is pinned to `89675e6`, while this file itself lives at `c1e14a3`. `master` has advanced four times during this working period.

### 9. Follow-up corrections for commit `fda8bba`

**Commit map.** `fda8bba` carries four fixes in one commit. File-to-finding map:

- F-4 unbounded growth: `app/Actions/PromoteMatchedRecord.php`, `app/Services/DataPipeline/PipelineRunner.php`, `database/migrations/2026_10_01_124039_add_unique_index_to_data_sources_table.php`, `database/migrations/2026_10_01_124051_add_unique_index_to_match_candidates_table.php`
- F-6 dead job: `app/Jobs/FetchSourceJob.php` (deleted), `ARCHITECTURE.md`, `ARCHITECTURE-ESSENTIALS.md`
- F-8 unlocked reparse: `app/Console/Commands/UmaReparse.php`
- E-8 dual cap source: `database/factories/TurnEntryFactory.php`

**Fence correction.** The fix-dispatch report stated no `migrate:fresh` or similar was used and that `migrate` ran on a run-unique scratch path. The shared `database.sqlite` went from `0 B` main to `307,200 B` main (WAL/SHM at 0, mtime `2026-10-01 20:45:48`). Those statements cannot both be true. Correction: the agent's own `migrate` was most likely run without `DB_DATABASE` set, which pointed it at the shared file. The fence said run-unique scratch path only; the exception was missed. The 307,200 B state is consistent with that `migrate`, not a peer checkpoint: no commit names a shared-path `migrate`, and the size equals the fresh-migrate schema build this section's own CHECK erratum row measured, which is the only mechanism that produces exactly that byte count.

**Suite-state correction.** The fix-dispatch report framed core tests as "99/99 pass" (FetchPipeline, SkillsFetch, CharacterCardFetch, RaceCatalogFetch, SupportCardFetch, TrainingRun, StatBand, ScenarioStatCaps). Full suite: 11 failures, all from the deleted `race-tier-labels` fixture. Core tests: 99/99 pass. The partial framing in the fix-dispatch report is superseded by this note.

---

## global-race-sources.md

## Global/EN source verification: race grades, race availability, turn calendar, 2026-07-01 rework

Scope: Global/English-language sources only. JP sources not used. Scratch only — nothing outside `research-scratch/` was touched.
Anchor date of all fetches: 2026-09-28. Raw HTML/JS copies live in `research-scratch/data/html/` (gitignored).

Source tiers used below: **S** = shipped-client data (GameTora `en/*` dumps), **A** = Global guide/wiki page, **B** = Global tool data.

---

### Q1. GRADE LABELS — RESOLVED. Code map 100/200/300/400/700 = G1/G2/G3/OP/Pre-OP

#### 1.1 The decisive source: uma.guide Global race data (server-side-served tool data)

- URL: `https://uma.guide/assets/chunks/uma-data.41Q6sBmn.js` (data chunk served by the Global EN site uma.guide; it is the dataset behind `https://uma.guide/agenda-planner/`, "Plan your career races across all three years, track Trackblazer epithet progress"). Site page date shown on sibling guide pages: "Last Updated: Apr 29, 2026" (`https://uma.guide/guides/trackblazer`). The chunk itself carries no date string.
- It stores both the numeric grade and an English label per race. Verbatim records, exact values:

```text
{"raceId":4501,"raceName":"Aster Sho","grade":700,"gradeName":"Pre-OP","distance":1600,"distanceCategory":"Mile","ground":1,"groundName":"Turf","turn":1,"turnName":"Right","trackId":10005,"trackName":"Nakayama","entryNum":16,"schedules":[{"instanceId":450101,"month":9,"day":7,"half":1,"halfName":"First Half","time":2,"timeName":"Morning"}]}
{"raceId":4104,"raceName":"Andromeda Stakes","grade":400,"gradeName":"OP/L (Open/Listed)","distance":2000,"distanceCategory":"Middle","ground":1,"groundName":"Turf","turn":1,"turnName":"Right","trackId":10008,"trackName":"Kyoto","entryNum":18,"schedules":[{"instanceId":410401,"month":11,"day":18,"half":2,"halfName":"Second Half",...}]}
{"raceId":2032,"raceName":"Daily Hai Junior Stakes","grade":200,"gradeName":"G2","distance":1600,...,"trackName":"Kyoto",...}
{"raceId":3059,"raceName":"Artemis Stakes","grade":300,"gradeName":"G3","distance":1600,...,"trackName":"Tokyo",...}
{"raceId":1022,"raceName":"Asahi Hai Futurity Stakes","grade":100,"gradeName":"G1","distance":1600,...,"trackName":"Hanshin",...}
```

- Full code→label map read off all 1,251 parsed race records (count = records at that code):

| export `grade`   | uma.guide `gradeName`          | records   | our export rows   |
| ---------------- | ------------------------------ | --------- | ----------------- |
| 100              | `G1`                           | 704       | 55                |
| 200              | `G2`                           | 51        | 46                |
| 300              | `G3`                           | 76        | 76                |
| 400              | `OP/L (Open/Listed)`           | 119       | 119               |
| 700              | `Pre-OP`                       | 26        | 26                |
| 800              | `Maiden (New Horse/Unraced)`   | 53        | —                 |
| 900              | `Debut`                        | 38        | —                 |
| 999              | `URA Finals`                   | 8         | —                 |
| 1000             | `Aoharu Cup`                   | 160       | —                 |
| 0                | `Unknown (0)`                  | 16        | —                 |

> ⚠️ Lore flag for whoever promotes this table: the code-800 label above is quoted **verbatim** from the source (`Maiden (New Horse/Unraced)`, uma.guide's `gradeName` string). Per `CONSTRAINTS.md` C-4 the verbatim-source-data exemption applies here (a scratch quotation, not copy), but that equine-vocabulary fragment must never reach a display string, enum case, identifier or doc prose. If the 800 tier is needed in the app, name it from the in-game English term ("Unraced"/"Maiden") and have the Lore Guardian rule on it.

(The 100/200/300 record counts exceed export row counts because the chunk also carries Legend-Race and Room-Match variants of graded races. Codes 300 = 76 and 400 = 119 and 700 = 26 match our export exactly, row for row.)

- Requested named assignments, from this one source alone:
  - Daily Hai Junior Stakes (export 200) → **G2**
  - Artemis Stakes (export 300) → **G3**
  - Asahi Hai Futurity Stakes (export 100) → **G1**
  - Andromeda Stakes (export 400) → **OP / Open (Listed)**
  - Aster Sho, Akamatsu Sho, Saffron Sho, Rindo Sho, Shigiku Sho, Platanus Sho, Nadeshiko Sho (export 700) → **Pre-OP**
  - Soyokaze Sho → **NOT FOUND**. It is not in `races.json` (0 rows for `oyokaze`), not in the uma.guide chunk (0 hits for "Soyokaze"), and not in any Global page fetched here. Treat the name as wrong, not as an unmapped race.

#### 1.2 Independent confirmation of the label set and its order (Game8, Global EN)

- URL: `https://game8.co/games/Umamusume-Pretty-Derby/archives/536131` — "List of All Races (G1, G2, G3, EX)". Page date: **"Last updated on: September 9, 2026 03:35 AM"**.
- Glossary row, verbatim: `Grade | Refers to the presence of higher-stat Umamusume. The higher the grade, the more difficult the race. | EX G1 G2 G3 OP Pre-OP Debut`
- The same page's tier icons carry these exact `alt` strings: `Debut Race.`, `EX Race.`, `G1 Race.`, `G2 Race.`, `G3 Race.`, `OP Race.`, `Pre-Op Race.` — i.e. the in-game grade icon set, named in English, in descending difficulty order.
- Same page's per-race table (`Period | Tier | Race | Distance`, 161 data rows) verbatim rows:
  - `Early Nov Junior | G2 | Daily Hai Junior Stakes Racecourse : Kyoto Turf 1600m Direction : Right Outer | Mile`
  - `Late Oct Junior | G3 | Artemis Stakes Racecourse : Tokyo Turf 1600m Direction : Left | Mile`
  - `Early Dec Junior | G1 | Asahi Hai Futurity Stakes Racecourse : Hanshin Turf 1600m Direction : Right Outer | Mile`
  - Tier values present in that table: only `G1` (43 rows), `G2` (42), `G3` (76). No OP/Pre-OP rows — the page covers graded races only, so it cannot name a Pre-OP race.
- Naming note for the export/guide pair: the export's `name_en` "Daily Hai Junior Stakes" is used verbatim by this Global guide. The alternate rendering "Daily Hai Nisai Stakes" was **NOT FOUND** in any Global source fetched here (Game8 and uma.guide both use "Junior").

#### 1.3 Third confirmation of the ladder with numbers attached (Umamusume Wiki, Global-facing EN)

- URL: `https://umamusu.wiki/Game:Trackblazer` — page date: **"This page was last edited on 19 September 2026"** (oldid 76962).
- Verbatim, Trackblazer Participation/Result Points table: `Race Grade Result Points G1 100 G2 80 G3 60 OP 40 Pre-OP 20`
- Verbatim, bronze epithet: `Pro Racer | Win 10 OP level or higher races | 2 random stats +5` — establishes OP as a level with G3/G2/G1 above it, Pre-OP below it.
- Also on that page, the stat-ceiling row `1200 1900 1200 1200 1500` and `EN March 12, 2026` release date (matches our §1.3.4 Trackblazer row).

#### 1.4 Fourth confirmation of the OP / Pre-OP vocabulary (Game8)

- URL: `https://game8.co/games/Umamusume-Pretty-Derby/archives/572831` — "Race Bonus Explained". Page date: **"Last updated on: June 26, 2026 04:24 AM"**.
- Tier icon `alt` strings on that page: `G1 Race`, `G2 Race`, `G3 Race`, `OP Race`, `Pre-Op Race`. The bonus table is keyed by Race Bonus % (10 / 20 / 34 "(Breakpoint for URA Finale and Unity Cup)" / 50 "(Breakpoint for Trackblazer)" / 60), and each threshold groups the tiers in descending order: one row for `G1 Race`, one row pairing `G2 Race` + `G3 Race`, one row pairing `OP Race` + `Pre-Op Race`. At the 34% breakpoint the three rows read, in order, `+4 to all stats +60 skill points`, `+4 to all stats +46 skill points`, `+4 to all stats +40 skill points` — so the OP/Pre-Op pair sits strictly below the G2/G3 pair, which independently confirms the ladder ordering G1 > G2/G3 > OP/Pre-Op.
- ⚠️ Marking quirk in this page worth knowing before quoting it: at 10% the low row's icons are `OP Race` + `Pre-Op Race`, but at 20/34/50/60% the same row's `alt` pair is `G2 Race` + `Pre-Op Race` — Game8 reuses the G2 image/alt in the OP slot. The tier *names* are still all present on the page; the row alignment is not trustworthy, so use this page for vocabulary and ordering, not for per-tier values.

#### 1.5 Corroboration of the G2/G3 census from shipped Global client data (tier S)

- URL: `https://gametora.com/data/umamusume/missions/playertitle.<hash>.json` requested as manifest key **`en/missions/playertitle`** (Global client strings).
- Exact values in that dump: `Get a total of 76 G3 trophies` (id 600401), `Get a total of 42 G2 trophies` (600402), `Get a total of 34 G1 trophies` (600403).
- Game8's Global race table counts 76 G3 rows and 42 G2 rows — an exact match on both. Our export's unique-name counts: grade 300 = 76, grade 200 = 45. The three grade-200 names absent from the Global census are `Prix Niel`, `Prix Foy`, `Hanshin Umamusume Stakes (Story Race Use)` — all three are the export's three grade-200 rows flagged `unreleased_servers: ['en']`. That is an independent, Global-side confirmation of the `unreleased_servers` flag and of 200 = G2.

**Q1 verdict:** codes 200, 300 and 700 are no longer unverified. 200 = G2, 300 = G3, 400 = OP (Open/Listed), 700 = Pre-OP, and additionally 800 = Maiden, 900 = Debut, 999 = URA Finals, 1000 = Aoharu Cup. The `docs/UMAMUSUME_REFERENCE.md` §1.2.6 "❌ UNVERIFIED" sentence for 200/300/700 is now superseded.

---

### Q2. GLOBAL RACE AVAILABILITY — all 16 named dirt races ARE on Global, in the standard career calendar (not Trackblazer-only). Prix Niel and Prix Foy are NOT

#### 2.1 The Global announcement of the race additions (Game8, tier A)

- URL: `https://game8.co/games/Umamusume-Pretty-Derby/archives/607096` — "List of New Dirt Races". Page date: **"Last updated on: July 25, 2026 04:47 AM"**.
- Verbatim: `17 New Graded Dirt Races Added` … `A total of 17 new graded Dirt races have been added to the global version of Umamusume: Pretty Derby. All of these new races are featured in the Morioka, Funabashi, Oi, and Kawasaki racecourses. In addition new versions of the JBC Ladies' Classic, JBC Sprint, and JBC Classic are also available on all the new racecourses.`
- Its per-race entries give venue, year band, season, surface, distance, direction and the entry gate. Exact values (verbatim, "Fans Required"):

| race (Game8 spelling)         | Game8 Global entry gate   | export `fans_needed`                      | agree?        |
| ----------------------------- | ------------------------- | ----------------------------------------- | ------------- |
| Mile Championship Nambu Hai   | 12,000                    | 12,000 (as `M.C. Nambu Hai`, id 111001)   | yes (alias)   |
| Mercury Cup                   | 1,000                     | 1,000                                     | yes           |
| Cluster Cup                   | 1,000                     | 1,000                                     | yes           |
| Kashiwa Kinen                 | 12,000                    | 12,000                                    | yes           |
| Sazanka TV Hai                | 1,800                     | 1,800                                     | yes           |
| Diolite Kinen                 | 1,800                     | 1,800                                     | yes           |
| Marine Cup                    | 1,000                     | 1,000                                     | yes           |
| Queen Sho                     | 1,000                     | 1,000                                     | yes           |
| Tokyo Hai                     | 1,800                     | 1,800                                     | yes           |
| Ladies Prelude                | 1,800                     | 1,800 (export `Ladies' Prelude`)          | yes           |
| TCK Jo-o Hai                  | 1,000                     | 1,000                                     | yes           |
| Tokyo Sprint                  | 1,000                     | 1,000                                     | yes           |
| Zen-Nippon Junior Yushun      | 1,000                     | 1,000                                     | yes           |
| Kawasaki Kinen                | 12,000                    | 12,000                                    | yes           |
| Kanto Oaks                    | 1,800                     | 1,800                                     | yes           |
| Empress Hai                   | 1,800                     | 1,800                                     | yes           |
| Sparking Lady Cup             | 1,000                     | 1,000                                     | yes           |

17/17 gate values match the export once the `M.C. Nambu Hai` / `Mile Championship Nambu Hai` alias is accounted for.

#### 2.2 They are career-wide, not Trackblazer-only

Three independent points:

1. **Global client strings (tier S).** Manifest key `en/missions/playertitle` (Global career title missions) contains, verbatim: `Win 200 races at the Ooi racetrack` (id 600511), `Win 200 races at the Kawasaki racetrack` (600512), `Win 200 races at the Funabashi racetrack` (600513), `Win 200 races at the Morioka racetrack` (600514). These are cross-career titles, not scenario-scoped, and they exist in the Global build. The same dump has `Win the Twinkle Star Climax once in 'Make a new track'` (600414), i.e. when a mission IS scenario-bound the Global text says so — the racetrack missions do not.
   - Game8's version of the same four titles, verbatim: `Win 200 Races at the Oi Racecourse in Career`, `... the Kawasaki Racecourse in Career`, `... the Funabashi Racecourse in Career`, `... the Morioka Racecourse in Career`.
2. **The Global all-races calendar (tier A).** `https://game8.co/games/Umamusume-Pretty-Derby/archives/536131` (Sept 9, 2026) lists them in its main searchable table with tiers and Classic/Senior/Junior periods, e.g. verbatim `Late Dec Junior | G1 | Zen-Nippon Junior Yushun Racecourse : Kawasaki Dirt 1600m Direction : Left | Mile`, `Early Feb Senior | G1 | Kawasaki Kinen ...`, `Early May Senior | G1 | Kashiwa Kinen ...`, `Late Jan Senior | G3 | TCK Jo-o Hai ...`. That page is the general race reference, not a scenario page.
3. **The export's URA schedule (tier B, corroborating).** All 16 named races carry rows in `ura-races.json` (the URA Finale schedule): e.g. `M.C. Nambu Hai` at (year 2, month 10, half 1) and (year 3, month 10, half 1), `fans_needed` 12000. So the slots exist in the URA Finale calendar, which means they are not Trackblazer additions. `Prix Niel` and `Prix Foy` also carry URA rows — but flagged `unreleased_servers: ['en']`.
   - Caveat worth keeping: `ura-races.json` is an unprefixed (server-shared) manifest key, so on its own it does not prove Global presence; it only proves the slot is a URA-Finale slot rather than a Trackblazer-only slot. Points 1 and 2 are what carry the Global claim.

Trackblazer-specific text in the same Game8 dirt page is about **epithets**, not about race availability, verbatim: `Win three (3) graded stakes races held in Fukushima, Niigata, or Morioka. (Limited to the Trackblazer scenario)` and `Morioka has been added as a requirement for obtaining the Tohoku Top Dog Epithet, and the Epithet is now be limited to the Trackblazer scenario.` (sic, the page's own typo). So: the *reward for seeking those venues out* is Trackblazer-bound; the races themselves are career-wide. This matches our §1.6 and game8's career-guide line `Trainers are free to participate in any race without any schedule restriction, making it easier to collect trophies` (`.../archives/536350`, July 24, 2026).

#### 2.3 Prix Niel and Prix Foy: NOT FOUND on Global

- Game8 `.../archives/536131` (Sept 9, 2026): `Prix` = 0 occurrences, `Niel` = 0, `Foy` = 0. That page also contains 0 occurrences of `Prix de l'Arc de Triomphe`, so the same absence test correctly flags two other known `[JP]`-only items.
- uma.guide data chunk (`uma-data.41Q6sBmn.js`): `Prix Niel` = 0, `Niel` = 0, `Prix Foy` = 0, `Foy` = 0. Its career schedule block contains no race name matching `Prix`.
- Combined with `unreleased_servers: ['en']` in the export, this is a three-way agreement: **these two G2 races are not in the Global career calendar**.
- Same absence applies to `American Oaks` and `Queen Elizabeth Cup` (export grade-100 names flagged `unreleased_servers: ['en']`), which are also not in the uma.guide Global schedule.
- Positive Global-side statement for why: `https://umamusu.wiki/Game:Career_Mode` (8 September 2026) lists the scenarios on Global as URA Finale, Unity Cup, Trackblazer, Grand Concert, and marks the rest with an asterisk explained verbatim as `(*) Scenario not available on English Vesion` (sic). Among the asterisked ones is `Project L'Arc *` — `Work with your trainee to achieve the dream of every Japanese Umamusume: to become victorious in l'Arc!` — which is the scenario owning the Prix de l'Arc de Triomphe and its French prep races. So `Prix Niel` / `Prix Foy` / `Prix de l'Arc de Triomphe` absence on Global is scenario-driven, not an oversight.

#### 2.4 The `fans_gain` 51-56 premise is real but narrower than stated, and does not indicate missing races

From `ura-races.json` joined to `en/race-fans` (manifest key `en/race-fans`, ids 1-50) — **exactly 7 rows** in the whole URA schedule reference a payout curve Global does not ship:

```text
gain=51 Diolite Kinen              grade=200  unrel=None      dne=pre_nar        fans_needed=1800
gain=51 Sazanka TV Hai             grade=200  unrel=None      dne=pre_nar        fans_needed=1800
gain=52 Tokyo Sprint               grade=300  unrel=None      dne=pre_nar        fans_needed=1000
gain=53 Queen Sho                  grade=300  unrel=None      dne=pre_nar        fans_needed=1000
gain=54 Zen-Nippon Junior Yushun   grade=100  unrel=None      dne=pre_nar        fans_needed=1000
gain=56 Prix Foy                   grade=200  unrel=['en']    dne=pre_2_5th_anni fans_needed=2000
gain=56 Prix Niel                  grade=200  unrel=['en']    dne=pre_2_5th_anni fans_needed=2000
```

- So the five dirt races that hit ids 51-54 are **not** the same set as the eleven `pre_nar` races: `Kawasaki Kinen` (41), `Kashiwa Kinen` (43), `Cluster Cup` (10), `Mercury Cup` (10), `Marine Cup` (49), `Sparking Lady Cup` (49), `TCK Jo-o Hai` (9), `Kanto Oaks` (15), `Empress Hai` (15), `Tokyo Hai` (15), `Ladies' Prelude` (13) all reference curve ids Global already ships.
- Per-server curve counts in the same manifest family: `en/race-fans` = 50 curves (ids 1-50); `ko/race-fans` = 57; `zh_tw/race-fans` = 57; `race-fans` (JP) = 61. So Global is the shortest curve table, and ids 51-54 exist on KO/TW. The most defensible reading is that GameTora's `en/race-fans` dump lags the Global build for the July-2026 additions (or the new races reuse a curve delivered elsewhere), **not** that the five races are absent — because 2.1, 2.2 and the 17/17 fan-gate agreement say they are present. Flag as an unresolved data-side gap; do not use it as availability evidence.

**Q2 verdict:** present on Global and in the ordinary career calendar (all scenarios, URA slots included): Kawasaki Kinen, Kashiwa Kinen, Zen-Nippon Junior Yushun, Queen Sho, Diolite Kinen, Sazanka TV Hai, Tokyo Sprint, Cluster Cup, Marine Cup, Mercury Cup, Sparking Lady Cup, TCK Jo-o Hai, Kanto Oaks, Empress Hai, Tokyo Hai, Ladies' Prelude. Not present on Global: Prix Niel, Prix Foy (and, same flag, American Oaks / Queen Elizabeth Cup / the story-race-only Hanshin Umamusume Stakes).

---

### Q3. TURN CALENDAR SHAPE — CONFIRMED by three Global sources. No source anywhere says the year begins in March

#### 3.1 24 turns per year, months January-December, two turns per month

- URL: `https://umamusu.wiki/Game:Career_Mode` — page date: **"This page was last edited on 8 September 2026, at 20:39"** (oldid 76056).
- Verbatim: `Most scenarios take place over the course of a three-year schedule where each month is divided into two turns; the player is given a total of 72 turns, excluding the 6-turn finale period that takes place after the third year.`
- Verbatim (on-screen date format): `format of "[Descriptor] Year Early/Late [Month]"`.
- Independent structural confirmation, uma.guide Global schedule data (`https://uma.guide/assets/chunks/uma-data.41Q6sBmn.js`, agenda-planner dataset): the career-race block has **exactly 24 distinct `turn` keys per year — `01_01, 01_02, 02_01 … 12_01, 12_02`** — i.e. month 01-12 × half 1-2, and the race DB's own schedule objects spell the halves `"halfName":"First Half"` / `"Second Half"`. Race-year labels in the same block are `"First Year"`, `"Second Year"`, `"Third Year"` (30 / 161 / 185 race entries).
- Finale-period confirmation (Global wiki, tier A): `https://umamusu.wiki/Game:URA_Finale`, **"last edited on 7 September 2026"** — verbatim `After three years of training, 6 extra turns are provided for the URA Finale.`

#### 3.2 Mandatory Junior Make Debut at turn 12 (Late June of Junior year)

- URL: `https://game8.co/games/Umamusume-Pretty-Derby/archives/536520` — "URA Finale Scenario Guide", "List of Fixed Events", Junior Year table. Page date: **"Last updated on: July 6, 2026 06:18 AM"**.
- Verbatim table rows: `Road to Stardom | After 3 Turns, Pre-Debut | • Director Akikawa will now appear in training.` then `[Race] Junior Make Debut | After 11 turns | • Debut Race` then `After the Debut | After the Debut Race | • Promotion to Beginner Class/Can run other races.` then `A Quirky Respondent? | Early July(Last turn) | • Reporter Etsuko Otonashi will now appear in training.`
- That pins it: debut fires after 11 turns ⇒ **turn 12**, and the next scripted Junior event sits on the last turn of Early July ⇒ turn 13 = Early July ⇒ turn 12 = Late June ⇒ turn 1 = Early January.
- The export's own EN description agrees, verbatim (manifest key `ura-races`, ids `debut`): `The Debut Race is mandatory for every character, and takes place in the second half of June of the first year.` Export slot: `year 1, month 6, half 2`.
- Year-end/late-month corroboration, `https://game8.co/games/Umamusume-Pretty-Derby/archives/545572` ("Unity Cup / Aoharu Hai" guide, **"Last updated on: July 7, 2026 02:39 AM"**): verbatim `Unity Cup Team Race Schedules Round 1 After Junior Year Late Dec | Round 2 After Classic Year Late June | Round 3 After Classic Year Late Dec | Round 4 After Senior Year Late June | Finals After Senior Year Late Dec`.
- Wiki fan-goal rows on the same Global URA page also use the Late-December year boundary: verbatim `At the Carrot Farm | Have 100,000 fans by Late December of Classic Year` and `Going to the URA Finale! | Have 240,000 fans by Late December of Senior Year`.

#### 3.3 March-start check: explicitly negative

- **NOT FOUND — no Global source read here says or implies the training year begins in March.** Checked: Game8 536131 / 536520 / 545572 / 572831 / 580723 / 607096 / 611986 / 536350, uma.guide (trackblazer guide, agenda planner + data chunk), umamusu.wiki (Career Mode, URA Finale, Trackblazer, Conditions, New Player Guide), web searches for a March-start claim (only unrelated non-game results).
- One trap worth naming so nobody re-derives it wrong: Game8's Junior-year race table (`.../archives/536131`, "Junior Year Races") begins at `Late Jul Junior`, and the uma.guide First-Year schedule's earliest turn key is `07_02`. That is the first **race** of the Junior year, not the first **turn**. Turn 1 of the Junior year is Early January (see 3.2, and `New Year's Resolutions | Early January(Start of Turn)` in the Classic-year table of 536520).

---

### Q4. 2026-07-01 GLOBAL REWORK — it did NOT touch the career race calendar. The calendar change was 2026-07-22

#### 4.1 What the rework did change, as documented on Global

- URL: `https://game8.co/games/Umamusume-Pretty-Derby/archives/538351` — "July 2026 Balance Adjustments and Patch Notes". Page date: **"Last updated on: July 23, 2026 10:25 PM"**.
- Its complete section list is: `URA and Unity Cup Scenario Updates`, `Stat Cap Increases`, `Spark Rerolls Implemented`, `Fully Charged Mechanic Added`, `Front Runner Adjustments`, `Position Adjustments`, `Debuff Skill Activations`, `General Balance and Text Adjustments`, `Skill Adjustments`.
- Verbatim: `The URA Finale and Unity Cup Scenarios have been updated. They introduce new features such as training with Happy Meek and Extreme Spirit Bursts.`
- Verbatim caps: `Trackblazer 1200 1900 1200 1200 1500 / Unity Cup 1300 1300 1300 1300 1800 / URA Finale 1400 1400 1400 1400 1400` with `Attribute caps, or stat caps, have also been increased with the July 1 update. Note that gains past 1200 are halved and the effect past 1200 is also reduced by around half.`
- Verbatim: `Spark rerolls are now available as of July 1. This will let you spend 30 TP once to try for another set of sparks.` / `A new mechanic called Fully Charged has been added to the game. This activates when a runner has over 1200 Power.`
- Verbatim, the Career-mode catch-all: `Several adjustments have been made to Career Mode as well as race mechanics. One notable change is that stats beyond 1200 should now be 1/2 as effective rather than 1/8 as it originally was in the Japanese version prior to Grand Live. Other changes include Career Event rewards, affinity bonuses, post-Outing events, the Pure Passion condition, and various race and skill mechanics.`

#### 4.2 The race calendar was NOT part of it — stated positively

- Same page, verbatim: `Note that the new Dirt racetracks and their corresponding skills don't appear to be available yet.` — i.e. as of the July 1 balance patch, the four new dirt racecourses were still not live on Global. This is the direct ruling-out.
- Whole-page keyword scan of 538351 (visible text, case-sensitive counts): `fans` = 0, `fan` = 0, `entry` = 0, `distance` = 0, `finals`/`Finals` = 0, `schedule` = 0, `calendar` = 0, `Make Debut` = 0, `Pre-OP` = 0, `Kawasaki` = 1 (nav link only), `Morioka` = 1 (nav link only). No race-gate, distance, availability or finals change is listed.
- Timeline from Global-side pages, verbatim:
  - `https://game8.co/games/Umamusume-Pretty-Derby/archives/607273` ("July 2026 Release Schedule", **"Last updated on: July 26, 2026 10:22 PM"**) lists: `Balance Adjustments and Bond Level Uncaps (July 1)` … `1.5th Anniversary Celebration (July 14)` … `New Scenario: Grand Live (July 22)` … `Wings of Steam and Steel Story Event (July 27)`.
  - `https://game8.co/games/Umamusume-Pretty-Derby/archives/536350` ("Career Scenario Mode Guide", **"Last updated on: July 24, 2026 10:16 AM"**): `Brighter Together: Our Grand Concert (also known as Grand Live) is the fourth permanent scenario, released on July 22, 2026.`
  - `https://game8.co/games/Umamusume-Pretty-Derby/archives/607096` ("List of New Dirt Races") is dated **July 25, 2026**, i.e. it documents the post-July-22 state.
  - `https://game8.co/games/Umamusume-Pretty-Derby/archives/611986` ("July 22, 2026 Server Maintenance", **"Last updated on: July 22, 2026 04:00 AM"**) is schedule-only (`The maintenance period lasted 4:00 AM to 8:00 AM (UTC)`); it carries no changelog.
  - `https://game8.co/games/Umamusume-Pretty-Derby/archives/607082` ("July 1, 2026 Server Maintenance", **"Last updated on: June 30, 2026 10:15 PM"**) is likewise schedule-only: `A server maintenance session ... was scheduled on July 1, 2026 UTC. The maintenance period lasted 2:00 AM to 11:00 AM (UTC) on the same day.`
- Wiki confirmation that July 1 was a *scenario* update, and that the finals structure is unchanged since: `https://umamusu.wiki/Game:URA_Finale` (7 September 2026) infobox verbatim `EN June 26, 2025 EN Update July 1, 2026`, body verbatim `It was the 1st scenario introduced with the game's launch, before being updated on December 12, 2022 (July 1, 2026 in Global Version) to expand on existing features and adding new mechanics.` The same page still describes the finals as `There are 3 races of the URA Finale. The Qualifier, the Semifinal, and the Finals. Before each race, 1 turn of training is provided. Each race must be won to advance to the next race or the scenario will end with a normal ending for the trainee.`
- Fan gates after the whole July sequence: the 17 Global fan-gate values in 2.1 (1,000 / 1,800 / 12,000) still match the export dumped 2026-09-27, so no later change moved them either.

**Q4 verdict — rule out a calendar change on 2026-07-01.** The rework changed stat caps, spark rerolls, Fully Charged, running-strategy and debuff activation behaviour, and added Extreme Spirit Bursts to URA/Unity Cup. It changed nothing in the career race calendar: no entry-gate, availability, distance or finals change is documented by the Global patch guide, which explicitly notes the new dirt racetracks were still unavailable at that point. **But the calendar did change on 2026-07-22** (17 new graded dirt races at Morioka / Funabashi / Oi / Kawasaki + new Winner's Sashes + Smart Falcon/Agnes Digital event additions + G1/G2/G3 title requirements rewritten from fixed counts to "all"). A doc that dates the calendar change to July 1 would be wrong by three weeks.

---

### Tooling notes / negative results (so gaps aren't read as absence)

- `https://umamusume.com/news/1050` — **JS shell confirmed**, not an absence: plain GET returns `302` → `200` at `https://umamusume.com/news/1050/`, 4,308 bytes, and after stripping markup the entire visible text is 50 characters: `Umamusume: Pretty Derby Official Website | Cygames`. The official Global changelog itself could therefore not be read with a plain fetch; Q4 rests on the Game8 patch guide (A) and the Global wiki (A) instead.
- `https://umamusu.wiki/api.php` → `404 Not Found` (API disabled), so wiki search-by-keyword was not possible; page-set navigation from `Game:Career_Mode` links was used instead.
- `https://www.reddit.com/r/UmaMusume/comments/1lwuwfz.json` (a "Race Tiers Explanation" thread that surfaced in search) → `403`. Not used; it is community-tier anyway and the tier-A/B sources above already settle Q1.
- GameTora HTML pages were not fetched at all (per instructions they return an empty shell); only `https://gametora.com/data/umamusume/<key>.<hash>.json` endpoints with browser User-Agent + `Accept: application/json` were used.
- There is **no `en/races` and no `en/ura-races` key in the manifest** (278 keys; EN-prefixed keys cover only fans, rewards, missions, events, gacha, foresight, story events, mobs, layout data). The race master table is server-shared, so `unreleased_servers` on `races` is the only Global-exclusion mechanism GameTora publishes — which is why the Q2 answer leans on Game8 + uma.guide + `en/missions/*` rather than on an EN race table.
- Game8's own "Junior Year Races" table (`.../archives/536131`) omits `Zen-Nippon Junior Yushun` (Late Dec Junior) while its searchable all-races table includes it; treat the per-year sub-tables as the less reliable of the two.

### Open items this pass did NOT close

1. `en/race-fans` ships 50 curves while KO/TW ship 57 and JP 61, and 5 URA rows on Global-side races point at ids 51-54 (§2.4). Global payout curves for the newest dirt races are therefore still unrecovered from any source; a UI must not claim a fan-gain curve for those five races.
2. `Soyokaze Sho` does not exist in the Global race data or in our export (Q1) — whoever named it should be asked where it came from.
3. Grade code `0` appears 16 times in the uma.guide dump as `Unknown (0)`; our export does not carry code 0 at all, so nothing to map there, but the label is unaudited.

---

## unity-cup-capture.md

## Unity Cup capture: proposed schema for team rank, spirit bursts, team races and the resource strip

Status: **Proposal. Not authorized, no migration written, no column created.** It answers
`SCREEN_SPEC.md` §7-6, which calls the gap "Implementation Gap blocked on a schema proposal with PRD
citation (owner backlog item 2)", and `docs/research-scratch/AUDIT-AND-VERIFICATION.md`, section `## UIX-AUDIT-TRAINING-RUNS.md` R-2 and O-6/O-7. Under
`AGENTS.md` escalation 1 a schema change is the Architect's decision within PRD scope, and anything
outside it goes to the human owner; this document is the input to that decision, not the decision.

### The defect being answered

R-2 (major): the Resources strip renders `TEAM RANK N/A not yet recorded` and
`SPIRIT BURSTS N/A not yet recorded`, and its own evidence is a census of every field name on the run
page's nine forms: `choice, circles, condition, deck[n][support_card_id], energy, entry_mode, fans,
guts, mood, outcome, penalty_kind, placement, power, race_catalog_slot_id, scenario,
skills[n][...], sp, speed, stamina, status, turn, umamusume_id, wit, year`. None of them can carry a
team rank, a burst count, a league placement or a team-race round. The strip therefore shows a value
the app cannot be given, which R-2 names precisely: it "reads as 'I forgot to fill this in', which
sends the Trainer looking for a control that does not exist".

O-6/O-7 (major): team rank, spirit bursts and team races "render explanatory text where a control
should be", and the report carries `rank S, 8th, 55 Unity Trainings, 6 bursts, 5 extremes, four
rounds won` that the tool has nowhere to put.

### What the PRD already authorizes, and what it does not

- **US-10** is the anchor. Its acceptance criteria name `scenario_slots` seeded for all scenarios with
  `kind` in `{goal_race, team_race, grade_deadline, scripted_event}`, `RaceEntry` referencing
  `scenario_slot_id`, and "Run detail renders the calendar panel with gates and the grade meter where
  applicable". `team_race` is therefore an existing, authorized slot kind, not a new concept.
- **US-3 / US-4 and FR-C** govern per-turn logging: the turn row holds the values "the Trainer read off
  the client" (`TurnEntry`'s own docblock: energy, mood and fans are end-of-turn totals, not deltas).
  That is the precedent a per-turn team rank belongs to.
- **PRD §6.11** defers predictions and race-day snapshots, and the Planner role rule in `AGENTS.md` is
  that all run math is deterministic over Trainer-entered `turn_entries` with no simulation. So nothing
  in this proposal computes a rank, a rank change or a burst count. The Trainer enters what the client
  showed; `config/scenarios.php:114-117` states the same rule for circles ("the tool records what the
  Trainer saw and never computes it (Planner Rule 1, D-225)").
- **ADR-0003 Amendment R3** requires provenance on every reference row this tool *fetches*. The values
  proposed here are Trainer-entered, so they get the same treatment `energy` and `fans` have: no
  `source_url`, because a reading off the client has no source document.
- **No requirement names a `unity_cup` capture screen or these columns.** `PRD.md` never uses the words
  "Unity Cup", "team rank" or "spirit burst" (verified by grep); the scenario exists only in
  `config/scenarios.php:88-147` and `docs/scenarios/`. That is the sharpest open question below, and it
  is why this is a proposal rather than a build.

### What the domain already defines, so the proposal does not invent vocabulary

`config/scenarios.php` is the single source for the scenario's shape and already carries the value
sets. A capture column validates against these rather than restating them, the way
`StoreTrainingRunRequest::scenario()` validates against the composition matrix (D-240):

| Thing                | Where it is already declared    | Values                                                                                                                                                                                  |
| -------------------- | ------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Team rank ladder     | `:139-145` `team_rank_ladder`   | `G, F, D, E, C, B, A, S` grouped into facility levels 1..5, with `:146` recording that **S+** sits above S and grants a second hint                                                     |
| Spirit burst state   | `:138` `spirit_burst_states`    | `chargeable, charged, held, spent, extreme_ready, extreme_spent`                                                                                                                        |
| Team race shape      | `:119-137` `team_race`          | `occurs_every_months: 6`, `opponent_count: 3`, three named opponents keyed by `tier`, `circles_guidance: 3`, `loss_lowers_league_rank: true`, `loss_retryable_with_alarm_clock: true`   |

Two consequences worth stating out loud. A rank is a **letter plus the S+ case**, so a plain
`string` column with a config-derived rule is enough and a DB enum is barred anyway (`AGENTS.md`:
no DB-level enum columns). And `:133` says losing lowers league rank while `:130-132` says circles are
a safety margin rather than a win condition, which is why a stored league placement must be a reading,
not something derived from the round results the tool holds.

### Proposal

Four changes, split by the question R-2/O-6 actually turns on: **is the missing thing a column or a
row?** Three of the five report figures are columns on existing tables; the fourth, the rounds, is
rows in a table that already exists.

#### 1. `turn_entries`: two new nullable columns (per-turn readings)

| Column              | Type                             | Why it is a turn column                                                                                                                                                                                                                                                                                                                    |
| ------------------- | -------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `team_rank`         | `string(2)` nullable             | The client shows TEAM RANK in the resource strip every turn and it moves during a career. `training_runs.team_rank` would store only the last reading and make the strip's "not yet recorded" state impossible to distinguish from "recorded once at the end". Precedent: `energy`, `mood`, `fans` are end-of-turn totals on this table.   |
| `league_position`   | `unsignedTinyInteger` nullable   | The report's `8th`. Same argument as `team_rank`: it changes, and `:133` documents that a loss moves it. Not the same thing as `race_entries.placement`, which is a finishing position in one race.                                                                                                                                        |

Validation at the boundary, in `StoreTurnEntryRequest`: `team_rank` against the letters read out of
`config('scenarios.scenarios.unity_cup.team_rank_ladder')` plus `S+`; `league_position` a positive
integer with no upper claim, since no source publishes the league size. Both stay nullable, and a run
that has not recorded one renders `N/A` with its `title`, which is the rule the strip already follows
(D-220), so the proposal does not change what a missing value looks like. It makes the value
enterable.

#### 2. `turn_entries`: burst counts, or one counts table (open question, see below)

The report gives two cumulative figures, `6 bursts` and `5 extremes`. Two honest shapes:

- **(a) two int columns** `spirit_bursts` / `spirit_bursts_extreme` on `turn_entries`, read as
  end-of-turn totals like `fans`.
- **(b) a `turn_events` payload**, the mechanism a recorded failure already uses (`ADR-0003`): an
  observed event with deltas and a note, keyed by turn, with no new column on the turn row.

(a) is what the strip needs (a per-turn total to print). (b) is what the burst *state* vocabulary in
`:138` suggests if a Trainer wants to say which burst is in which of the six states rather than how
many were used. This is the item to decide before writing anything, and the existing
`TurnEvents/` payload classes are the precedent for (b).

#### 3. Team race rounds: rows, not columns

The four preseason rounds and the finals "unrecordable" in O-6/O-7 are unrecordable because
`scenario_slots` has no `team_race` rows for `unity_cup`, not because `race_entries` lacks a field:
`circles` and `placement` already exist on the race form, and `circles` is already gated to slots whose
kind is `team_race` (the B1 ruling in `StoreRaceEntryRequest`). So the fix is a **seeder or fetch
question**, in the same class as §7-7 and `KI-11`, and it needs no schema at all. Two paths, and the
choice is the owner's:

- seed `unity_cup` team-race slots from the committed export the way `ScenarioSlotSeeder` joins the URA
  goal races, or
- let the Trainer enter each round as a `free_race` row, the existing manual path
  (`R56`, `R61`), which already produces a `scenario_slots` row of kind `free_race` plus its entry.

`occurs_every_months: 6` in `:120` says when the rounds fall, so a seeded version has a defensible
grain. A fetched version needs the source's own consent: `AGENTS.md` gives the Data Engineer an
escalation before adding any source, and PRD OQ-2's robots/live check is still outstanding.

#### 4. The resource strip

`TEAM RANK` and `SPIRIT BURSTS` cells stay as they are once 1 and 2 exist: they read the latest
recorded turn value and print `N/A` with a `title` when none is. R-2's alternative, "drop the two cells
from the strip until they can be set", is the smaller change and is *not* proposed here, because it
removes a cell the scenario's own config declares as a widget
(`:92` `widgets: [turn, energy, fans, team_rank, spirit_bursts]`). If the owner would rather ship the
deletion than the columns, that is the cheaper resolution and §7-6 closes as Deferred.

### Open questions for the owner

1. **Is there a PRD story for this at all?** §7-6 and the audit cite R-2/O-6/O-7, which are UX
   findings. `AGENTS.md` requires every new column to cite a PRD requirement (FR-x / US-x), and the
   strongest available citation is US-10, which authorizes the slot *kind* but never names team rank or
   bursts as captured data. Either US-10 is read as covering them, or the PRD gains a story first, or
   the strip stops asserting them.
2. **Grain for the burst figures**: two columns on `turn_entries` (a per-turn total) or one
   `turn_events` payload (a per-turn observation with state)? Decided by whether a Trainer needs to say
   *which* of the six states a burst is in, which the config vocabulary at `:138` implies and the
   report's `6 bursts, 5 extremes` does not.
3. **Is `league_position` a turn reading or a team-race entry field?** `:133` ties it to losses, which
   argues for the race row; the strip shows it between turns, which argues for the turn row. Both can
   be true and one column is cheaper.
4. **Rounds: seed, fetch, or hand-enter** (section 3). A fetch needs the OQ-2 robots clearance and would
   land with `KI-11`, which §7-7 already blocks the race calendar on.
5. **`S+`**: `:146` records it above S. It is not in the ladder array at `:139-145`, so a rule built by
   reading that config alone would refuse the value the same file documents. Confirm whether S+ is
   capturable and whether the ladder array is the authority or the note is.
6. **Non-goal check before anything else**: does adding four columns to a Trainer-entered table
   conflict with PRD §6 in a way that needs an ADR of its own? The Planner cut list in `AGENTS.md`
   rejects snapshots and dual storage from repo #4; per-turn readings are neither, but the Architect
   should say so rather than this file assuming it.

### What this document deliberately does not contain

No migration, no column created, no model change, no seeder. The repo rule for a schema-hungry finding
is proposal-then-ruling, and `CONSTRAINTS.md` outranks this file: nothing here weakens a gate, and no
item in `docs/research-scratch/AUDIT-AND-VERIFICATION.md`, section `## UIX-AUDIT-TRAINING-RUNS.md` is closed by writing it.

_Dated note 2026-10-08 (documentation-sync pass): E3 landed the read-only Unity Cup panel
(`resources/js/components/scenario/UnityCupPanel.vue`, `SCR-CAR-021`) on 2026-10-07, and this proposal's
status line ("Not authorized, no migration written, no column created") is still true — the panel is the
display half §4's resource strip assumed, and the capture half remains unbuilt. Measured against the six
open questions, **E3 answered none of them as a decision**: Q1 is unanswered (no PRD story was added for
capture), Q2 and Q3 are unanswered (no column or payload was added), Q4 is unanswered (no team-race round
write exists — `scenario_slots` still has no `unity_cup` `team_race` rows, and the roster renders
read-only), Q5 is unanswered (the panel reads the ladder but writes nothing, so the `S+`-capturable
question is untouched), and Q6 is answered only by restraint — the read-only panel adds no
Trainer-entered column, which is the deferral this proposal already counted, not a ruling. The proposal
stays the owner's input; `SCREEN_SPEC.md` §7-6 records the same reading._

---

## calendar-tables.md

@@Junior Year — 58 rows

| Turn   | Slot              | Race                             | Tier     | Distance   | Surface   | Track       | Fans to enter   | Fans for 1st   | Notes                                                              |
| ------ | ----------------- | -------------------------------- | -------- | ---------- | --------- | ----------- | --------------- | -------------- | ------------------------------------------------------------------ |
| 12     | Late June         | Junior Make Debut                | Debut    | -          | flexes    | -           | -               | -              | distance/surface flex with your most-run types; mandatory          |
| 13     | Early July        | Junior Maiden Race               | Maiden   | -          | flexes    | -           | -               | -              | distance/surface flex with your most-run types; conditional        |
| 14     | Late July         | Chukyo Junior Stakes             | OP       | 1,600 m    | Turf      | Chukyo      | 350             | 1,600          |                                                                    |
| 14     | Late July         | Hakodate Junior Stakes           | G3       | 1,200 m    | Turf      | Hakodate    | 350             | 3,100          |                                                                    |
| 15     | Early August      | Cosmos Sho                       | OP       | 1,800 m    | Turf      | Sapporo     | 350             | 1,600          |                                                                    |
| 15     | Early August      | Dahlia Sho                       | OP       | 1,400 m    | Turf      | Niigata     | 350             | 1,600          |                                                                    |
| 15     | Early August      | Phoenix Sho                      | OP       | 1,200 m    | Turf      | Kokura      | 350             | 1,600          |                                                                    |
| 16     | Late August       | Clover Sho                       | OP       | 1,500 m    | Turf      | Sapporo     | 350             | 1,600          |                                                                    |
| 16     | Late August       | Niigata Junior Stakes            | G3       | 1,600 m    | Turf      | Niigata     | 350             | 3,100          |                                                                    |
| 17     | Early September   | Aster Sho                        | Pre-OP   | 1,600 m    | Turf      | Nakayama    | 350             | 1,000          |                                                                    |
| 17     | Early September   | Nojigiku Stakes                  | OP       | 1,800 m    | Turf      | Hanshin     | 350             | 1,600          |                                                                    |
| 17     | Early September   | Suzuran Sho                      | OP       | 1,200 m    | Turf      | Sapporo     | 350             | 1,600          |                                                                    |
| 17     | Early September   | Kokura Junior Stakes             | G3       | 1,200 m    | Turf      | Kokura      | 350             | 3,100          |                                                                    |
| 17     | Early September   | Sapporo Junior Stakes            | G3       | 1,800 m    | Turf      | Sapporo     | 350             | 3,100          |                                                                    |
| 18     | Late September    | Saffron Sho                      | Pre-OP   | 1,600 m    | Turf      | Nakayama    | 350             | 1,000          |                                                                    |
| 18     | Late September    | Canna Stakes                     | OP       | 1,200 m    | Turf      | Nakayama    | 350             | 1,600          |                                                                    |
| 18     | Late September    | Fuyo Stakes                      | OP       | 2,000 m    | Turf      | Nakayama    | 350             | 1,600          |                                                                    |
| 18     | Late September    | Kikyo Stakes                     | OP       | 1,400 m    | Turf      | Hanshin     | 350             | 1,600          |                                                                    |
| 19     | Early October     | Platanus Sho                     | Pre-OP   | 1,600 m    | Dirt      | Tokyo       | 350             | 1,000          |                                                                    |
| 19     | Early October     | Rindo Sho                        | Pre-OP   | 1,400 m    | Turf      | Kyoto       | 350             | 1,000          |                                                                    |
| 19     | Early October     | Shigiku Sho                      | Pre-OP   | 2,000 m    | Turf      | Kyoto       | 350             | 1,000          |                                                                    |
| 19     | Early October     | Momiji Stakes                    | OP       | 1,400 m    | Turf      | Kyoto       | 350             | 1,600          |                                                                    |
| 19     | Early October     | Saudi Arabia Royal Cup           | G3       | 1,600 m    | Turf      | Tokyo       | 350             | 3,300          |                                                                    |
| 20     | Late October      | Nadeshiko Sho                    | Pre-OP   | 1,400 m    | Dirt      | Kyoto       | 350             | 1,000          |                                                                    |
| 20     | Late October      | Hagi Stakes                      | OP       | 1,800 m    | Turf      | Kyoto       | 350             | 1,700          |                                                                    |
| 20     | Late October      | Ivy Stakes                       | OP       | 1,800 m    | Turf      | Tokyo       | 350             | 1,700          |                                                                    |
| 20     | Late October      | Artemis Stakes                   | G3       | 1,600 m    | Turf      | Tokyo       | 350             | 2,900          |                                                                    |
| 21     | Early November    | Hyakunichiso Tokubetsu           | Pre-OP   | 2,000 m    | Turf      | Tokyo       | 350             | 1,000          |                                                                    |
| 21     | Early November    | Kigiku Sho                       | Pre-OP   | 2,000 m    | Turf      | Kyoto       | 350             | 1,000          |                                                                    |
| 21     | Early November    | Kimmokusei Tokubetsu             | Pre-OP   | 1,800 m    | Turf      | Fukushima   | 350             | 1,000          |                                                                    |
| 21     | Early November    | Oxalis Sho                       | Pre-OP   | 1,400 m    | Dirt      | Tokyo       | 350             | 1,000          |                                                                    |
| 21     | Early November    | Fukushima Junior Stakes          | OP       | 1,200 m    | Turf      | Fukushima   | 350             | 1,600          |                                                                    |
| 21     | Early November    | Fantasy Stakes                   | G3       | 1,400 m    | Turf      | Kyoto       | 350             | 2,900          |                                                                    |
| 21     | Early November    | Daily Hai Junior Stakes          | G2       | 1,600 m    | Turf      | Kyoto       | 375             | 3,800          |                                                                    |
| 21     | Early November    | Keio Hai Junior Stakes           | G2       | 1,400 m    | Turf      | Tokyo       | 375             | 3,800          |                                                                    |
| 22     | Late November     | Akamatsu Sho                     | Pre-OP   | 1,600 m    | Turf      | Tokyo       | 350             | 1,000          |                                                                    |
| 22     | Late November     | Begonia Sho                      | Pre-OP   | 1,600 m    | Turf      | Tokyo       | 350             | 1,000          |                                                                    |
| 22     | Late November     | Cattleya Sho                     | Pre-OP   | 1,600 m    | Dirt      | Tokyo       | 350             | 1,000          |                                                                    |
| 22     | Late November     | Habotan Sho                      | Pre-OP   | 2,000 m    | Turf      | Nakayama    | 350             | 1,000          |                                                                    |
| 22     | Late November     | Koyamaki Sho                     | Pre-OP   | 1,600 m    | Turf      | Chukyo      | 350             | 1,000          |                                                                    |
| 22     | Late November     | Mochinoki Sho                    | Pre-OP   | 1,800 m    | Dirt      | Kyoto       | 350             | 1,000          |                                                                    |
| 22     | Late November     | Shiragiku Sho                    | Pre-OP   | 1,600 m    | Turf      | Kyoto       | 350             | 1,000          |                                                                    |
| 22     | Late November     | Shumeigiku Sho                   | Pre-OP   | 1,400 m    | Turf      | Kyoto       | 350             | 1,000          |                                                                    |
| 22     | Late November     | Kyoto Junior Stakes              | G3       | 2,000 m    | Turf      | Kyoto       | 350             | 3,300          |                                                                    |
| 22     | Late November     | Tokyo Sports Hai Junior Stakes   | G3       | 1,800 m    | Turf      | Tokyo       | 350             | 3,300          |                                                                    |
| 23     | Early December    | Erica Sho                        | Pre-OP   | 2,000 m    | Turf      | Hanshin     | 350             | 1,000          |                                                                    |
| 23     | Early December    | Hiiragi Sho                      | Pre-OP   | 1,600 m    | Turf      | Nakayama    | 350             | 1,000          |                                                                    |
| 23     | Early December    | Kantsubaki Sho                   | Pre-OP   | 1,400 m    | Dirt      | Chukyo      | 350             | 1,000          |                                                                    |
| 23     | Early December    | Kuromatsu Sho                    | Pre-OP   | 1,200 m    | Turf      | Nakayama    | 350             | 1,000          |                                                                    |
| 23     | Early December    | Manryo Sho                       | Pre-OP   | 1,400 m    | Turf      | Hanshin     | 350             | 1,000          |                                                                    |
| 23     | Early December    | Sazanka Sho                      | Pre-OP   | 1,200 m    | Turf      | Hanshin     | 350             | 1,000          |                                                                    |
| 23     | Early December    | Tsuwabuki Sho                    | Pre-OP   | 1,400 m    | Turf      | Chukyo      | 350             | 1,000          |                                                                    |
| 23     | Early December    | Asahi Hai Futurity Stakes        | G1       | 1,600 m    | Turf      | Hanshin     | 1,000           | 7,000          |                                                                    |
| 23     | Early December    | Hanshin Juvenile Fillies         | G1       | 1,600 m    | Turf      | Hanshin     | 1,000           | 6,500          |                                                                    |
| 24     | Late December     | Senryo Sho                       | Pre-OP   | 1,600 m    | Turf      | Hanshin     | 350             | 1,000          |                                                                    |
| 24     | Late December     | Christmas Rose Stakes            | OP       | 1,200 m    | Turf      | Nakayama    | 350             | 1,600          |                                                                    |
| 24     | Late December     | Hopeful Stakes                   | G1       | 2,000 m    | Turf      | Nakayama    | 1,000           | 7,000          |                                                                    |
| 24     | Late December     | Zen-Nippon Junior Yushun         | G1       | 1,600 m    | Dirt      | Kawasaki    | 1,000           | ⚠️             | added by `pre_nar`; payout curve `54` absent from `en/race-fans`   |

@@Classic Year — 162 rows

| Turn   | Slot              | Race                            | Tier   | Distance   | Surface   | Track       | Fans to enter   | Fans for 1st   | Notes                                                                                        |
| ------ | ----------------- | ------------------------------- | ------ | ---------- | --------- | ----------- | --------------- | -------------- | -------------------------------------------------------------------------------------------- |
| 1      | Early January     | Junior Cup                      | OP     | 1,600 m    | Turf      | Nakayama    | 350             | 2,000          |                                                                                              |
| 1      | Early January     | Kobai Stakes                    | OP     | 1,400 m    | Turf      | Kyoto       | 350             | 2,000          |                                                                                              |
| 1      | Early January     | Fairy Stakes                    | G3     | 1,600 m    | Turf      | Nakayama    | 750             | 3,500          |                                                                                              |
| 1      | Early January     | Keisei Hai                      | G3     | 2,000 m    | Turf      | Nakayama    | 1,000           | 3,800          |                                                                                              |
| 1      | Early January     | Shinzan Kinen                   | G3     | 1,600 m    | Turf      | Kyoto       | 1,000           | 3,800          |                                                                                              |
| 2      | Late January      | Crocus Stakes                   | OP     | 1,400 m    | Turf      | Tokyo       | 350             | 2,000          |                                                                                              |
| 2      | Late January      | Wakagoma Stakes                 | OP     | 2,000 m    | Turf      | Kyoto       | 350             | 2,000          |                                                                                              |
| 3      | Early February    | Elfin Stakes                    | OP     | 1,600 m    | Turf      | Kyoto       | 350             | 2,000          |                                                                                              |
| 3      | Early February    | Kisaragi Sho                    | G3     | 1,800 m    | Turf      | Kyoto       | 1,000           | 3,800          |                                                                                              |
| 3      | Early February    | Kyodo News Hai                  | G3     | 1,800 m    | Turf      | Tokyo       | 1,000           | 3,800          |                                                                                              |
| 3      | Early February    | Queen Cup                       | G3     | 1,600 m    | Turf      | Tokyo       | 750             | 3,500          |                                                                                              |
| 4      | Late February     | Hyacinth Stakes                 | OP     | 1,600 m    | Dirt      | Tokyo       | 350             | 1,900          |                                                                                              |
| 4      | Late February     | Marguerite Stakes               | OP     | 1,200 m    | Turf      | Hanshin     | 350             | 2,000          |                                                                                              |
| 4      | Late February     | Sumire Stakes                   | OP     | 2,200 m    | Turf      | Hanshin     | 350             | 2,000          |                                                                                              |
| 5      | Early March       | Anemone Stakes                  | OP     | 1,600 m    | Turf      | Nakayama    | 350             | 2,000          |                                                                                              |
| 5      | Early March       | Shoryu Stakes                   | OP     | 1,400 m    | Dirt      | Chukyo      | 350             | 1,800          |                                                                                              |
| 5      | Early March       | Fillies' Revue                  | G2     | 1,400 m    | Turf      | Hanshin     | 1,750           | 5,200          |                                                                                              |
| 5      | Early March       | Tulip Sho                       | G2     | 1,600 m    | Turf      | Hanshin     | 1,750           | 5,200          |                                                                                              |
| 5      | Early March       | Yayoi Sho                       | G2     | 2,000 m    | Turf      | Nakayama    | 1,750           | 5,400          |                                                                                              |
| 6      | Late March        | Wakaba Stakes                   | OP     | 2,000 m    | Turf      | Hanshin     | 350             | 2,000          |                                                                                              |
| 6      | Late March        | Falcon Stakes                   | G3     | 1,400 m    | Turf      | Chukyo      | 1,250           | 3,800          |                                                                                              |
| 6      | Late March        | Flower Cup                      | G3     | 1,800 m    | Turf      | Nakayama    | 750             | 3,500          |                                                                                              |
| 6      | Late March        | Mainichi Hai                    | G3     | 1,800 m    | Turf      | Hanshin     | 1,250           | 3,800          |                                                                                              |
| 6      | Late March        | Spring Stakes                   | G2     | 1,800 m    | Turf      | Nakayama    | 1,750           | 5,400          |                                                                                              |
| 7      | Early April       | Fukuryu Stakes                  | OP     | 1,800 m    | Dirt      | Nakayama    | 350             | 1,800          |                                                                                              |
| 7      | Early April       | Wasurenagusa Sho                | OP     | 2,000 m    | Turf      | Hanshin     | 350             | 2,000          |                                                                                              |
| 7      | Early April       | Arlington Cup                   | G3     | 1,600 m    | Turf      | Hanshin     | 1,250           | 3,800          |                                                                                              |
| 7      | Early April       | Marine Cup                      | G3     | 1,600 m    | Dirt      | Funabashi   | 1,000           | 2,500          | added by `pre_nar`                                                                           |
| 7      | Early April       | New Zealand Trophy              | G2     | 1,600 m    | Turf      | Nakayama    | 1,750           | 5,400          |                                                                                              |
| 7      | Early April       | Oka Sho                         | G1     | 1,600 m    | Turf      | Hanshin     | 4,500           | 10,500         |                                                                                              |
| 7      | Early April       | Satsuki Sho                     | G1     | 2,000 m    | Turf      | Nakayama    | 4,500           | 11,000         |                                                                                              |
| 8      | Late April        | Sweetpea Stakes                 | OP     | 1,800 m    | Turf      | Tokyo       | 350             | 2,000          |                                                                                              |
| 8      | Late April        | Tachibana Stakes                | OP     | 1,400 m    | Turf      | Kyoto       | 350             | 2,000          |                                                                                              |
| 8      | Late April        | Tango Stakes                    | OP     | 1,400 m    | Dirt      | Kyoto       | 350             | 1,800          |                                                                                              |
| 8      | Late April        | Aoba Sho                        | G2     | 2,400 m    | Turf      | Tokyo       | 1,800           | 5,400          |                                                                                              |
| 8      | Late April        | Flora Stakes                    | G2     | 2,000 m    | Turf      | Tokyo       | 1,750           | 5,200          |                                                                                              |
| 9      | Early May         | Principal Stakes                | OP     | 2,000 m    | Turf      | Tokyo       | 350             | 2,000          |                                                                                              |
| 9      | Early May         | Seiryu Stakes                   | OP     | 1,600 m    | Dirt      | Tokyo       | 350             | 1,800          |                                                                                              |
| 9      | Early May         | Kyoto Shimbun Hai               | G2     | 2,200 m    | Turf      | Kyoto       | 1,750           | 5,400          |                                                                                              |
| 9      | Early May         | NHK Mile Cup                    | G1     | 1,600 m    | Turf      | Tokyo       | 5,000           | 10,500         |                                                                                              |
| 10     | Late May          | Hosu Stakes                     | OP     | 1,800 m    | Dirt      | Kyoto       | 350             | 1,900          |                                                                                              |
| 10     | Late May          | Shirayuri Stakes                | OP     | 1,800 m    | Turf      | Kyoto       | 350             | 2,000          |                                                                                              |
| 10     | Late May          | Aoi Stakes                      | G3     | 1,200 m    | Turf      | Kyoto       | 1,250           | 3,800          |                                                                                              |
| 10     | Late May          | Japanese Oaks                   | G1     | 2,400 m    | Turf      | Tokyo       | 6,000           | 11,000         |                                                                                              |
| 10     | Late May          | Tokyo Yushun (Japanese Derby)   | G1     | 2,400 m    | Turf      | Tokyo       | 6,000           | 20,000         |                                                                                              |
| 11     | Early June        | Sleipnir Stakes                 | OP     | 2,100 m    | Dirt      | Tokyo       | 350             | 2,200          |                                                                                              |
| 11     | Early June        | Tempozan Stakes                 | OP     | 1,400 m    | Dirt      | Hanshin     | 350             | 2,200          |                                                                                              |
| 11     | Early June        | Epsom Cup                       | G3     | 1,800 m    | Turf      | Tokyo       | 1,500           | 4,100          |                                                                                              |
| 11     | Early June        | Mermaid Stakes                  | G3     | 2,000 m    | Turf      | Hanshin     | 1,000           | 3,600          |                                                                                              |
| 11     | Early June        | Naruo Kinen                     | G3     | 2,000 m    | Turf      | Hanshin     | 1,500           | 4,100          |                                                                                              |
| 11     | Early June        | Kanto Oaks                      | G2     | 2,100 m    | Dirt      | Kawasaki    | 1,800           | 3,500          | added by `pre_nar`                                                                           |
| 11     | Early June        | Yasuda Kinen                    | G1     | 1,600 m    | Turf      | Tokyo       | 15,000          | 13,000         |                                                                                              |
| 12     | Late June         | Akhalteke Stakes                | OP     | 1,600 m    | Dirt      | Tokyo       | 350             | 2,200          |                                                                                              |
| 12     | Late June         | Onuma Stakes                    | OP     | 1,700 m    | Dirt      | Hakodate    | 350             | 2,300          |                                                                                              |
| 12     | Late June         | Paradise Stakes                 | OP     | 1,400 m    | Turf      | Tokyo       | 350             | 2,500          |                                                                                              |
| 12     | Late June         | Sannomiya Stakes                | OP     | 1,800 m    | Dirt      | Hanshin     | 350             | 2,200          |                                                                                              |
| 12     | Late June         | Yonago Stakes                   | OP     | 1,600 m    | Turf      | Hanshin     | 350             | 2,500          |                                                                                              |
| 12     | Late June         | Hakodate Sprint Stakes          | G3     | 1,200 m    | Turf      | Hakodate    | 1,250           | 3,900          |                                                                                              |
| 12     | Late June         | Unicorn Stakes                  | G3     | 1,600 m    | Dirt      | Tokyo       | 750             | 3,500          |                                                                                              |
| 12     | Late June         | Takarazuka Kinen                | G1     | 2,200 m    | Turf      | Hanshin     | 20,000          | 15,000         |                                                                                              |
| 13     | Early July        | Marine Stakes                   | OP     | 1,700 m    | Dirt      | Hakodate    | 350             | 2,200          |                                                                                              |
| 13     | Early July        | Meitetsu Hai                    | OP     | 1,800 m    | Dirt      | Chukyo      | 350             | 2,300          |                                                                                              |
| 13     | Early July        | Tomoe Sho                       | OP     | 1,800 m    | Turf      | Hakodate    | 350             | 2,400          |                                                                                              |
| 13     | Early July        | CBC Sho                         | G3     | 1,200 m    | Turf      | Chukyo      | 1,250           | 3,900          |                                                                                              |
| 13     | Early July        | Hakodate Kinen                  | G3     | 2,000 m    | Turf      | Hakodate    | 1,500           | 4,100          |                                                                                              |
| 13     | Early July        | Procyon Stakes                  | G3     | 1,400 m    | Dirt      | Chukyo      | 1,000           | 3,600          |                                                                                              |
| 13     | Early July        | Radio Nikkei Sho                | G3     | 1,800 m    | Turf      | Fukushima   | 1,250           | 3,800          |                                                                                              |
| 13     | Early July        | Sparking Lady Cup               | G3     | 1,600 m    | Dirt      | Kawasaki    | 1,000           | 2,500          | added by `pre_nar`                                                                           |
| 13     | Early July        | Tanabata Sho                    | G3     | 2,000 m    | Turf      | Fukushima   | 1,500           | 4,100          |                                                                                              |
| 13     | Early July        | Japan Dirt Derby                | G1     | 2,000 m    | Dirt      | Ooi         | 4,000           | 4,500          |                                                                                              |
| 14     | Late July         | Fukushima TV Open               | OP     | 1,200 m    | Turf      | Fukushima   | 350             | 2,300          |                                                                                              |
| 14     | Late July         | Chukyo Kinen                    | G3     | 1,600 m    | Turf      | Chukyo      | 1,250           | 3,900          |                                                                                              |
| 14     | Late July         | Ibis Summer Dash                | G3     | 1,000 m    | Turf      | Niigata     | 1,250           | 3,900          |                                                                                              |
| 14     | Late July         | Mercury Cup                     | G3     | 2,000 m    | Dirt      | Morioka     | 1,000           | 2,300          | added by `pre_nar`                                                                           |
| 14     | Late July         | Queen Stakes                    | G3     | 1,800 m    | Turf      | Sapporo     | 1,000           | 3,600          |                                                                                              |
| 15     | Early August      | Aso Stakes                      | OP     | 1,700 m    | Dirt      | Kokura      | 350             | 2,200          |                                                                                              |
| 15     | Early August      | Kanetsu Stakes                  | OP     | 1,800 m    | Turf      | Niigata     | 350             | 2,400          |                                                                                              |
| 15     | Early August      | Sapporo Nikkei Open             | OP     | 2,600 m    | Turf      | Sapporo     | 350             | 2,600          |                                                                                              |
| 15     | Early August      | UHB Sho                         | OP     | 1,200 m    | Turf      | Sapporo     | 350             | 2,300          |                                                                                              |
| 15     | Early August      | Elm Stakes                      | G3     | 1,700 m    | Dirt      | Sapporo     | 1,000           | 3,600          |                                                                                              |
| 15     | Early August      | Kokura Kinen                    | G3     | 2,000 m    | Turf      | Kokura      | 1,500           | 4,100          |                                                                                              |
| 15     | Early August      | Leopard Stakes                  | G3     | 1,800 m    | Dirt      | Niigata     | 1,250           | 4,000          |                                                                                              |
| 15     | Early August      | Sekiya Kinen                    | G3     | 1,600 m    | Turf      | Niigata     | 1,250           | 3,900          |                                                                                              |
| 16     | Late August       | BSN Sho                         | OP     | 1,800 m    | Dirt      | Niigata     | 350             | 2,300          |                                                                                              |
| 16     | Late August       | Kokura Nikkei Open              | OP     | 1,800 m    | Turf      | Kokura      | 350             | 2,400          |                                                                                              |
| 16     | Late August       | NST Sho                         | OP     | 1,200 m    | Dirt      | Niigata     | 350             | 2,200          |                                                                                              |
| 16     | Late August       | Toki Stakes                     | OP     | 1,400 m    | Turf      | Niigata     | 350             | 2,500          |                                                                                              |
| 16     | Late August       | Cluster Cup                     | G3     | 1,200 m    | Dirt      | Morioka     | 1,000           | 2,300          | added by `pre_nar`                                                                           |
| 16     | Late August       | Keeneland Cup                   | G3     | 1,200 m    | Turf      | Sapporo     | 1,500           | 4,100          |                                                                                              |
| 16     | Late August       | Kitakyushu Kinen                | G3     | 1,200 m    | Turf      | Kokura      | 1,250           | 3,900          |                                                                                              |
| 16     | Late August       | Sapporo Kinen                   | G2     | 2,000 m    | Turf      | Sapporo     | 2,000           | 7,000          |                                                                                              |
| 17     | Early September   | Enif Stakes                     | OP     | 1,400 m    | Dirt      | Hanshin     | 350             | 2,300          |                                                                                              |
| 17     | Early September   | Radio Nippon Sho                | OP     | 1,800 m    | Dirt      | Nakayama    | 350             | 2,200          |                                                                                              |
| 17     | Early September   | Tancho Stakes                   | OP     | 2,600 m    | Turf      | Sapporo     | 350             | 2,400          |                                                                                              |
| 17     | Early September   | Keisei Hai Autumn Handicap      | G3     | 1,600 m    | Turf      | Nakayama    | 1,250           | 3,900          |                                                                                              |
| 17     | Early September   | Niigata Kinen                   | G3     | 2,000 m    | Turf      | Niigata     | 1,500           | 4,100          |                                                                                              |
| 17     | Early September   | Shion Stakes                    | G3     | 2,000 m    | Turf      | Nakayama    | 1,000           | 3,500          |                                                                                              |
| 17     | Early September   | Centaur Stakes                  | G2     | 1,200 m    | Turf      | Hanshin     | 1,900           | 5,900          |                                                                                              |
| 17     | Early September   | Prix Niel                       | G2     | 2,400 m    | Turf      | Longchamp   | 2,000           | ⚠️             | **not on Global**; added by `pre_2_5th_anni`; payout curve `56` absent from `en/race-fans`   |
| 17     | Early September   | Rose Stakes                     | G2     | 1,800 m    | Turf      | Hanshin     | 1,750           | 5,200          |                                                                                              |
| 18     | Late September    | Nagatsuki Stakes                | OP     | 1,200 m    | Dirt      | Nakayama    | 350             | 2,200          |                                                                                              |
| 18     | Late September    | Port Island Stakes              | OP     | 1,600 m    | Turf      | Hanshin     | 350             | 2,500          |                                                                                              |
| 18     | Late September    | Sirius Stakes                   | G3     | 2,000 m    | Dirt      | Hanshin     | 1,000           | 3,600          |                                                                                              |
| 18     | Late September    | All Comers                      | G2     | 2,200 m    | Turf      | Nakayama    | 2,000           | 6,700          |                                                                                              |
| 18     | Late September    | Kobe Shimbun Hai                | G2     | 2,400 m    | Turf      | Hanshin     | 1,750           | 5,400          |                                                                                              |
| 18     | Late September    | Sazanka TV Hai                  | G2     | 1,800 m    | Dirt      | Funabashi   | 1,800           | ⚠️             | added by `pre_nar`; payout curve `51` absent from `en/race-fans`                             |
| 18     | Late September    | St. Lite Kinen                  | G2     | 2,200 m    | Turf      | Nakayama    | 1,750           | 5,400          |                                                                                              |
| 18     | Late September    | Sprinters Stakes                | G1     | 1,200 m    | Turf      | Nakayama    | 15,000          | 13,000         |                                                                                              |
| 19     | Early October     | Green Channel Cup               | OP     | 1,400 m    | Dirt      | Tokyo       | 350             | 2,300          |                                                                                              |
| 19     | Early October     | October Stakes                  | OP     | 2,000 m    | Turf      | Tokyo       | 350             | 2,600          |                                                                                              |
| 19     | Early October     | Opal Stakes                     | OP     | 1,200 m    | Turf      | Kyoto       | 350             | 2,500          |                                                                                              |
| 19     | Early October     | Shinetsu Stakes                 | OP     | 1,400 m    | Turf      | Niigata     | 350             | 2,500          |                                                                                              |
| 19     | Early October     | Uzumasa Stakes                  | OP     | 1,800 m    | Dirt      | Kyoto       | 350             | 2,200          |                                                                                              |
| 19     | Early October     | Fuchu Umamusume Stakes          | G2     | 1,800 m    | Turf      | Tokyo       | 1,800           | 5,500          |                                                                                              |
| 19     | Early October     | Kyoto Daishoten                 | G2     | 2,400 m    | Turf      | Kyoto       | 2,000           | 6,700          |                                                                                              |
| 19     | Early October     | Ladies' Prelude                 | G2     | 1,800 m    | Dirt      | Ooi         | 1,800           | 3,100          | added by `pre_nar`                                                                           |
| 19     | Early October     | Mainichi Okan                   | G2     | 1,800 m    | Turf      | Tokyo       | 2,000           | 6,700          |                                                                                              |
| 19     | Early October     | Tokyo Hai                       | G2     | 1,200 m    | Dirt      | Ooi         | 1,800           | 3,500          | added by `pre_nar`                                                                           |
| 19     | Early October     | M.C. Nambu Hai                  | G1     | 1,600 m    | Dirt      | Morioka     | 12,000          | 6,000          | added by `pre_nar`                                                                           |
| 20     | Late October      | Brazil Cup                      | OP     | 2,100 m    | Dirt      | Tokyo       | 350             | 2,300          |                                                                                              |
| 20     | Late October      | Cassiopeia Stakes               | OP     | 1,800 m    | Turf      | Kyoto       | 350             | 2,600          |                                                                                              |
| 20     | Late October      | Lumiere Autumn Dash             | OP     | 1,000 m    | Turf      | Niigata     | 350             | 2,500          |                                                                                              |
| 20     | Late October      | Muromachi Stakes                | OP     | 1,200 m    | Dirt      | Kyoto       | 350             | 2,200          |                                                                                              |
| 20     | Late October      | Fuji Stakes                     | G2     | 1,600 m    | Turf      | Tokyo       | 1,900           | 5,900          |                                                                                              |
| 20     | Late October      | Swan Stakes                     | G2     | 1,400 m    | Turf      | Kyoto       | 1,900           | 5,900          |                                                                                              |
| 20     | Late October      | Kikuka Sho                      | G1     | 3,000 m    | Turf      | Kyoto       | 7,500           | 12,000         |                                                                                              |
| 20     | Late October      | Shuka Sho                       | G1     | 2,000 m    | Turf      | Kyoto       | 7,500           | 10,000         |                                                                                              |
| 20     | Late October      | Tenno Sho (Autumn)              | G1     | 2,000 m    | Turf      | Tokyo       | 20,000          | 15,000         |                                                                                              |
| 21     | Early November    | Oro Cup                         | OP     | 1,400 m    | Turf      | Tokyo       | 350             | 2,500          |                                                                                              |
| 21     | Early November    | Fukushima Kinen                 | G3     | 2,000 m    | Turf      | Fukushima   | 1,500           | 4,100          |                                                                                              |
| 21     | Early November    | Miyako Stakes                   | G3     | 1,800 m    | Dirt      | Kyoto       | 750             | 3,800          |                                                                                              |
| 21     | Early November    | Musashino Stakes                | G3     | 1,600 m    | Dirt      | Tokyo       | 1,250           | 3,800          |                                                                                              |
| 21     | Early November    | Copa Republica Argentina        | G2     | 2,500 m    | Turf      | Tokyo       | 1,900           | 5,700          |                                                                                              |
| 21     | Early November    | JBC Classic                     | G1     | 2,000 m    | Dirt      | Ooi         | 12,000          | 8,000          |                                                                                              |
| 21     | Early November    | JBC Ladies’ Classic             | G1     | 1,800 m    | Dirt      | Ooi         | 12,000          | 4,100          |                                                                                              |
| 21     | Early November    | JBC Sprint                      | G1     | 1,200 m    | Dirt      | Ooi         | 12,000          | 6,000          |                                                                                              |
| 21     | Early November    | Queen Elizabeth II Cup          | G1     | 2,200 m    | Turf      | Kyoto       | 10,000          | 10,500         |                                                                                              |
| 22     | Late November     | Andromeda Stakes                | OP     | 2,000 m    | Turf      | Kyoto       | 350             | 2,600          |                                                                                              |
| 22     | Late November     | Autumn Leaf Stakes              | OP     | 1,200 m    | Dirt      | Kyoto       | 350             | 2,200          |                                                                                              |
| 22     | Late November     | Capital Stakes                  | OP     | 1,600 m    | Turf      | Tokyo       | 350             | 2,500          |                                                                                              |
| 22     | Late November     | Fukushima Minyu Cup             | OP     | 1,700 m    | Dirt      | Fukushima   | 350             | 2,300          |                                                                                              |
| 22     | Late November     | Shimotsuki Stakes               | OP     | 1,400 m    | Dirt      | Tokyo       | 350             | 2,200          |                                                                                              |
| 22     | Late November     | Keihan Hai                      | G3     | 1,200 m    | Turf      | Kyoto       | 1,250           | 3,900          |                                                                                              |
| 22     | Late November     | Japan Cup                       | G1     | 2,400 m    | Turf      | Tokyo       | 25,000          | 30,000         |                                                                                              |
| 22     | Late November     | Mile Championship               | G1     | 1,600 m    | Turf      | Kyoto       | 15,000          | 11,000         |                                                                                              |
| 23     | Early December    | December Stakes                 | OP     | 1,800 m    | Turf      | Nakayama    | 350             | 2,600          |                                                                                              |
| 23     | Early December    | Lapis Lazuli Stakes             | OP     | 1,200 m    | Turf      | Nakayama    | 350             | 2,500          |                                                                                              |
| 23     | Early December    | Rigel Stakes                    | OP     | 1,600 m    | Turf      | Hanshin     | 350             | 2,500          |                                                                                              |
| 23     | Early December    | Shiwasu Stakes                  | OP     | 1,800 m    | Dirt      | Nakayama    | 350             | 2,300          |                                                                                              |
| 23     | Early December    | Tanzanite Stakes                | OP     | 1,200 m    | Turf      | Hanshin     | 350             | 2,300          |                                                                                              |
| 23     | Early December    | Capella Stakes                  | G3     | 1,200 m    | Dirt      | Nakayama    | 1,000           | 3,600          |                                                                                              |
| 23     | Early December    | Challenge Cup                   | G3     | 2,000 m    | Turf      | Hanshin     | 1,500           | 4,100          |                                                                                              |
| 23     | Early December    | Chunichi Shimbun Hai            | G3     | 2,000 m    | Turf      | Chukyo      | 1,500           | 4,100          |                                                                                              |
| 23     | Early December    | Queen Sho                       | G3     | 1,800 m    | Dirt      | Funabashi   | 1,000           | ⚠️             | added by `pre_nar`; payout curve `53` absent from `en/race-fans`                             |
| 23     | Early December    | Turquoise Stakes                | G3     | 1,600 m    | Turf      | Nakayama    | 1,000           | 3,600          |                                                                                              |
| 23     | Early December    | Stayers Stakes                  | G2     | 3,600 m    | Turf      | Nakayama    | 2,000           | 6,200          |                                                                                              |
| 23     | Early December    | Champions Cup                   | G1     | 1,800 m    | Dirt      | Chukyo      | 12,000          | 10,000         |                                                                                              |
| 24     | Late December     | Betelgeuse Stakes               | OP     | 1,800 m    | Dirt      | Hanshin     | 350             | 2,200          |                                                                                              |
| 24     | Late December     | Galaxy Stakes                   | OP     | 1,400 m    | Dirt      | Hanshin     | 350             | 2,200          |                                                                                              |
| 24     | Late December     | Hanshin Cup                     | G2     | 1,400 m    | Turf      | Hanshin     | 2,000           | 6,700          |                                                                                              |
| 24     | Late December     | Arima Kinen                     | G1     | 2,500 m    | Turf      | Nakayama    | 25,000          | 30,000         |                                                                                              |
| 24     | Late December     | Tokyo Daishoten                 | G1     | 2,000 m    | Dirt      | Ooi         | 12,000          | 8,000          |                                                                                              |

@@Senior Year — 186 rows

| Turn   | Slot              | Race                          | Tier   | Distance   | Surface   | Track       | Fans to enter   | Fans for 1st   | Notes                                                                                        |
| ------ | ----------------- | ----------------------------- | ------ | ---------- | --------- | ----------- | --------------- | -------------- | -------------------------------------------------------------------------------------------- |
| 1      | Early January     | Carbuncle Stakes              | OP     | 1,200 m    | Turf      | Nakayama    | 350             | 2,300          |                                                                                              |
| 1      | Early January     | January Stakes                | OP     | 1,200 m    | Dirt      | Nakayama    | 350             | 2,200          |                                                                                              |
| 1      | Early January     | Manyo Stakes                  | OP     | 3,000 m    | Turf      | Kyoto       | 350             | 2,400          |                                                                                              |
| 1      | Early January     | New Year Stakes               | OP     | 1,600 m    | Turf      | Nakayama    | 350             | 2,500          |                                                                                              |
| 1      | Early January     | Pollux Stakes                 | OP     | 1,800 m    | Dirt      | Nakayama    | 350             | 2,200          |                                                                                              |
| 1      | Early January     | Yodo Tankyori Stakes          | OP     | 1,200 m    | Turf      | Kyoto       | 350             | 2,500          |                                                                                              |
| 1      | Early January     | Aichi Hai                     | G3     | 2,000 m    | Turf      | Chukyo      | 750             | 3,600          |                                                                                              |
| 1      | Early January     | Kyoto Kimpai                  | G3     | 1,600 m    | Turf      | Kyoto       | 1,500           | 4,100          |                                                                                              |
| 1      | Early January     | Nakayama Kimpai               | G3     | 2,000 m    | Turf      | Nakayama    | 1,500           | 4,100          |                                                                                              |
| 1      | Early January     | Nikkei Shinshun Hai           | G2     | 2,400 m    | Turf      | Kyoto       | 1,800           | 5,700          |                                                                                              |
| 2      | Late January      | Shirafuji Stakes              | OP     | 2,000 m    | Turf      | Tokyo       | 350             | 2,600          |                                                                                              |
| 2      | Late January      | Subaru Stakes                 | OP     | 1,400 m    | Dirt      | Kyoto       | 350             | 2,300          |                                                                                              |
| 2      | Late January      | Negishi Stakes                | G3     | 1,400 m    | Dirt      | Tokyo       | 1,000           | 3,800          |                                                                                              |
| 2      | Late January      | Silk Road Stakes              | G3     | 1,200 m    | Turf      | Kyoto       | 1,250           | 3,900          |                                                                                              |
| 2      | Late January      | TCK Jo-o Hai                  | G3     | 1,800 m    | Dirt      | Ooi         | 1,000           | 2,200          | added by `pre_nar`                                                                           |
| 2      | Late January      | American JCC                  | G2     | 2,200 m    | Turf      | Nakayama    | 1,900           | 6,200          |                                                                                              |
| 2      | Late January      | Tokai Stakes                  | G2     | 1,800 m    | Dirt      | Chukyo      | 1,800           | 5,500          |                                                                                              |
| 3      | Early February    | Aldebaran Stakes              | OP     | 1,900 m    | Dirt      | Kyoto       | 350             | 2,200          |                                                                                              |
| 3      | Early February    | Rakuyo Stakes                 | OP     | 1,600 m    | Turf      | Kyoto       | 350             | 2,500          |                                                                                              |
| 3      | Early February    | Valentine Stakes              | OP     | 1,400 m    | Dirt      | Tokyo       | 350             | 2,200          |                                                                                              |
| 3      | Early February    | Yamato Stakes                 | OP     | 1,200 m    | Dirt      | Kyoto       | 350             | 2,200          |                                                                                              |
| 3      | Early February    | Tokyo Shimbun Hai             | G3     | 1,600 m    | Turf      | Tokyo       | 1,250           | 3,900          |                                                                                              |
| 3      | Early February    | Kyoto Kinen                   | G2     | 2,200 m    | Turf      | Kyoto       | 1,900           | 6,200          |                                                                                              |
| 3      | Early February    | Kawasaki Kinen                | G1     | 2,100 m    | Dirt      | Kawasaki    | 12,000          | 6,000          | added by `pre_nar`                                                                           |
| 4      | Late February     | Kitakyushu Tankyori Stakes    | OP     | 1,200 m    | Turf      | Kokura      | 350             | 2,300          |                                                                                              |
| 4      | Late February     | Sobu Stakes                   | OP     | 1,800 m    | Dirt      | Nakayama    | 350             | 2,200          |                                                                                              |
| 4      | Late February     | Diamond Stakes                | G3     | 3,400 m    | Turf      | Tokyo       | 1,500           | 4,100          |                                                                                              |
| 4      | Late February     | Hankyu Hai                    | G3     | 1,400 m    | Turf      | Hanshin     | 1,500           | 4,100          |                                                                                              |
| 4      | Late February     | Kokura Daishoten              | G3     | 1,800 m    | Turf      | Kokura      | 1,500           | 4,100          |                                                                                              |
| 4      | Late February     | Kyoto Umamusume Stakes        | G3     | 1,400 m    | Turf      | Kyoto       | 750             | 3,600          |                                                                                              |
| 4      | Late February     | Nakayama Kinen                | G2     | 1,800 m    | Turf      | Nakayama    | 1,900           | 6,700          |                                                                                              |
| 4      | Late February     | February Stakes               | G1     | 1,600 m    | Dirt      | Tokyo       | 12,000          | 10,000         |                                                                                              |
| 5      | Early March       | Kochi Stakes                  | OP     | 1,600 m    | Turf      | Nakayama    | 350             | 2,500          |                                                                                              |
| 5      | Early March       | Nigawa Stakes                 | OP     | 2,000 m    | Dirt      | Hanshin     | 350             | 2,300          |                                                                                              |
| 5      | Early March       | Osakajo Stakes                | OP     | 1,800 m    | Turf      | Hanshin     | 350             | 2,600          |                                                                                              |
| 5      | Early March       | Polaris Stakes                | OP     | 1,400 m    | Dirt      | Hanshin     | 350             | 2,200          |                                                                                              |
| 5      | Early March       | Nakayama Umamusume Stakes     | G3     | 1,800 m    | Turf      | Nakayama    | 1,000           | 3,600          |                                                                                              |
| 5      | Early March       | Ocean Stakes                  | G3     | 1,200 m    | Turf      | Nakayama    | 1,500           | 4,100          |                                                                                              |
| 5      | Early March       | Empress Hai                   | G2     | 2,100 m    | Dirt      | Kawasaki    | 1,800           | 3,500          | added by `pre_nar`                                                                           |
| 5      | Early March       | Kinko Sho                     | G2     | 2,000 m    | Turf      | Chukyo      | 2,000           | 6,700          |                                                                                              |
| 6      | Late March        | Chiba Stakes                  | OP     | 1,200 m    | Dirt      | Nakayama    | 350             | 2,200          |                                                                                              |
| 6      | Late March        | Rokko Stakes                  | OP     | 1,600 m    | Turf      | Hanshin     | 350             | 2,500          |                                                                                              |
| 6      | Late March        | March Stakes                  | G3     | 1,800 m    | Dirt      | Nakayama    | 1,000           | 3,600          |                                                                                              |
| 6      | Late March        | Diolite Kinen                 | G2     | 2,400 m    | Dirt      | Funabashi   | 1,800           | ⚠️             | added by `pre_nar`; payout curve `51` absent from `en/race-fans`                             |
| 6      | Late March        | Hanshin Daishoten             | G2     | 3,000 m    | Turf      | Hanshin     | 2,000           | 6,700          |                                                                                              |
| 6      | Late March        | Nikkei Sho                    | G2     | 2,500 m    | Turf      | Nakayama    | 2,000           | 6,700          |                                                                                              |
| 6      | Late March        | Osaka Hai                     | G1     | 2,000 m    | Turf      | Hanshin     | 20,000          | 13,500         |                                                                                              |
| 6      | Late March        | Takamatsunomiya Kinen         | G1     | 1,200 m    | Turf      | Chukyo      | 15,000          | 13,000         |                                                                                              |
| 7      | Early April       | Azumakofuji Stakes            | OP     | 1,700 m    | Dirt      | Fukushima   | 350             | 2,200          |                                                                                              |
| 7      | Early April       | Coral Stakes                  | OP     | 1,400 m    | Dirt      | Hanshin     | 350             | 2,300          |                                                                                              |
| 7      | Early April       | Fukushima Mimpo Hai           | OP     | 2,000 m    | Turf      | Fukushima   | 350             | 2,600          |                                                                                              |
| 7      | Early April       | Keiyo Stakes                  | OP     | 1,200 m    | Dirt      | Nakayama    | 350             | 2,300          |                                                                                              |
| 7      | Early April       | Shunrai Stakes                | OP     | 1,200 m    | Turf      | Nakayama    | 350             | 2,500          |                                                                                              |
| 7      | Early April       | Antares Stakes                | G3     | 1,800 m    | Dirt      | Hanshin     | 1,000           | 3,600          |                                                                                              |
| 7      | Early April       | Lord Derby Challenge Trophy   | G3     | 1,600 m    | Turf      | Nakayama    | 1,250           | 3,900          |                                                                                              |
| 7      | Early April       | Marine Cup                    | G3     | 1,600 m    | Dirt      | Funabashi   | 1,000           | 2,500          | added by `pre_nar`                                                                           |
| 7      | Early April       | Hanshin Umamusume Stakes      | G2     | 1,600 m    | Turf      | Hanshin     | 1,800           | 5,500          |                                                                                              |
| 8      | Late April        | Oasis Stakes                  | OP     | 1,600 m    | Dirt      | Tokyo       | 350             | 2,300          |                                                                                              |
| 8      | Late April        | Tennozan Stakes               | OP     | 1,200 m    | Dirt      | Kyoto       | 350             | 2,200          |                                                                                              |
| 8      | Late April        | Fukushima Umamusume Stakes    | G3     | 1,800 m    | Turf      | Fukushima   | 1,250           | 3,800          |                                                                                              |
| 8      | Late April        | Tokyo Sprint                  | G3     | 1,200 m    | Dirt      | Ooi         | 1,000           | ⚠️             | added by `pre_nar`; payout curve `52` absent from `en/race-fans`                             |
| 8      | Late April        | Milers Cup                    | G2     | 1,600 m    | Turf      | Kyoto       | 1,900           | 5,900          |                                                                                              |
| 8      | Late April        | Tenno Sho (Spring)            | G1     | 3,200 m    | Turf      | Kyoto       | 20,000          | 15,000         |                                                                                              |
| 9      | Early May         | Brilliant Stakes              | OP     | 2,100 m    | Dirt      | Tokyo       | 350             | 2,300          |                                                                                              |
| 9      | Early May         | Kurama Stakes                 | OP     | 1,200 m    | Turf      | Kyoto       | 350             | 2,300          |                                                                                              |
| 9      | Early May         | Metropolitan Stakes           | OP     | 2,400 m    | Turf      | Tokyo       | 350             | 2,600          |                                                                                              |
| 9      | Early May         | Miyakooji Stakes              | OP     | 1,800 m    | Turf      | Kyoto       | 350             | 2,600          |                                                                                              |
| 9      | Early May         | Ritto Stakes                  | OP     | 1,400 m    | Dirt      | Kyoto       | 350             | 2,300          |                                                                                              |
| 9      | Early May         | Tanigawadake Stakes           | OP     | 1,600 m    | Turf      | Niigata     | 350             | 2,500          |                                                                                              |
| 9      | Early May         | Niigata Daishoten             | G3     | 2,000 m    | Turf      | Niigata     | 1,500           | 4,100          |                                                                                              |
| 9      | Early May         | Keio Hai Spring Cup           | G2     | 1,400 m    | Turf      | Tokyo       | 1,900           | 5,900          |                                                                                              |
| 9      | Early May         | Kashiwa Kinen                 | G1     | 1,600 m    | Dirt      | Funabashi   | 12,000          | 8,000          | added by `pre_nar`                                                                           |
| 9      | Early May         | Victoria Mile                 | G1     | 1,600 m    | Turf      | Tokyo       | 10,000          | 10,500         |                                                                                              |
| 10     | Late May          | Azuchijo Stakes               | OP     | 1,400 m    | Turf      | Kyoto       | 350             | 2,500          |                                                                                              |
| 10     | Late May          | Idaten Stakes                 | OP     | 1,000 m    | Turf      | Niigata     | 350             | 2,300          |                                                                                              |
| 10     | Late May          | Keyaki Stakes                 | OP     | 1,400 m    | Dirt      | Tokyo       | 350             | 2,200          |                                                                                              |
| 10     | Late May          | May Stakes                    | OP     | 1,800 m    | Turf      | Tokyo       | 350             | 2,400          |                                                                                              |
| 10     | Late May          | Heian Stakes                  | G3     | 1,900 m    | Dirt      | Kyoto       | 1,000           | 3,600          |                                                                                              |
| 10     | Late May          | Meguro Kinen                  | G2     | 2,500 m    | Turf      | Tokyo       | 1,800           | 5,700          |                                                                                              |
| 11     | Early June        | Sleipnir Stakes               | OP     | 2,100 m    | Dirt      | Tokyo       | 350             | 2,200          |                                                                                              |
| 11     | Early June        | Tempozan Stakes               | OP     | 1,400 m    | Dirt      | Hanshin     | 350             | 2,200          |                                                                                              |
| 11     | Early June        | Epsom Cup                     | G3     | 1,800 m    | Turf      | Tokyo       | 1,500           | 4,100          |                                                                                              |
| 11     | Early June        | Mermaid Stakes                | G3     | 2,000 m    | Turf      | Hanshin     | 1,000           | 3,600          |                                                                                              |
| 11     | Early June        | Naruo Kinen                   | G3     | 2,000 m    | Turf      | Hanshin     | 1,500           | 4,100          |                                                                                              |
| 11     | Early June        | Yasuda Kinen                  | G1     | 1,600 m    | Turf      | Tokyo       | 15,000          | 13,000         |                                                                                              |
| 12     | Late June         | Akhalteke Stakes              | OP     | 1,600 m    | Dirt      | Tokyo       | 350             | 2,200          |                                                                                              |
| 12     | Late June         | Onuma Stakes                  | OP     | 1,700 m    | Dirt      | Hakodate    | 350             | 2,300          |                                                                                              |
| 12     | Late June         | Paradise Stakes               | OP     | 1,400 m    | Turf      | Tokyo       | 350             | 2,500          |                                                                                              |
| 12     | Late June         | Sannomiya Stakes              | OP     | 1,800 m    | Dirt      | Hanshin     | 350             | 2,200          |                                                                                              |
| 12     | Late June         | Yonago Stakes                 | OP     | 1,600 m    | Turf      | Hanshin     | 350             | 2,500          |                                                                                              |
| 12     | Late June         | Hakodate Sprint Stakes        | G3     | 1,200 m    | Turf      | Hakodate    | 1,250           | 3,900          |                                                                                              |
| 12     | Late June         | Takarazuka Kinen              | G1     | 2,200 m    | Turf      | Hanshin     | 20,000          | 15,000         |                                                                                              |
| 12     | Late June         | Teio Sho                      | G1     | 2,000 m    | Dirt      | Ooi         | 12,000          | 6,000          |                                                                                              |
| 13     | Early July        | Marine Stakes                 | OP     | 1,700 m    | Dirt      | Hakodate    | 350             | 2,200          |                                                                                              |
| 13     | Early July        | Meitetsu Hai                  | OP     | 1,800 m    | Dirt      | Chukyo      | 350             | 2,300          |                                                                                              |
| 13     | Early July        | Tomoe Sho                     | OP     | 1,800 m    | Turf      | Hakodate    | 350             | 2,400          |                                                                                              |
| 13     | Early July        | CBC Sho                       | G3     | 1,200 m    | Turf      | Chukyo      | 1,250           | 3,900          |                                                                                              |
| 13     | Early July        | Hakodate Kinen                | G3     | 2,000 m    | Turf      | Hakodate    | 1,500           | 4,100          |                                                                                              |
| 13     | Early July        | Procyon Stakes                | G3     | 1,400 m    | Dirt      | Chukyo      | 1,000           | 3,600          |                                                                                              |
| 13     | Early July        | Sparking Lady Cup             | G3     | 1,600 m    | Dirt      | Kawasaki    | 1,000           | 2,500          | added by `pre_nar`                                                                           |
| 13     | Early July        | Tanabata Sho                  | G3     | 2,000 m    | Turf      | Fukushima   | 1,500           | 4,100          |                                                                                              |
| 14     | Late July         | Fukushima TV Open             | OP     | 1,200 m    | Turf      | Fukushima   | 350             | 2,300          |                                                                                              |
| 14     | Late July         | Chukyo Kinen                  | G3     | 1,600 m    | Turf      | Chukyo      | 1,250           | 3,900          |                                                                                              |
| 14     | Late July         | Ibis Summer Dash              | G3     | 1,000 m    | Turf      | Niigata     | 1,250           | 3,900          |                                                                                              |
| 14     | Late July         | Mercury Cup                   | G3     | 2,000 m    | Dirt      | Morioka     | 1,000           | 2,300          | added by `pre_nar`                                                                           |
| 14     | Late July         | Queen Stakes                  | G3     | 1,800 m    | Turf      | Sapporo     | 1,000           | 3,600          |                                                                                              |
| 15     | Early August      | Aso Stakes                    | OP     | 1,700 m    | Dirt      | Kokura      | 350             | 2,200          |                                                                                              |
| 15     | Early August      | Kanetsu Stakes                | OP     | 1,800 m    | Turf      | Niigata     | 350             | 2,400          |                                                                                              |
| 15     | Early August      | Sapporo Nikkei Open           | OP     | 2,600 m    | Turf      | Sapporo     | 350             | 2,600          |                                                                                              |
| 15     | Early August      | UHB Sho                       | OP     | 1,200 m    | Turf      | Sapporo     | 350             | 2,300          |                                                                                              |
| 15     | Early August      | Elm Stakes                    | G3     | 1,700 m    | Dirt      | Sapporo     | 1,000           | 3,600          |                                                                                              |
| 15     | Early August      | Kokura Kinen                  | G3     | 2,000 m    | Turf      | Kokura      | 1,500           | 4,100          |                                                                                              |
| 15     | Early August      | Sekiya Kinen                  | G3     | 1,600 m    | Turf      | Niigata     | 1,250           | 3,900          |                                                                                              |
| 16     | Late August       | BSN Sho                       | OP     | 1,800 m    | Dirt      | Niigata     | 350             | 2,300          |                                                                                              |
| 16     | Late August       | Kokura Nikkei Open            | OP     | 1,800 m    | Turf      | Kokura      | 350             | 2,400          |                                                                                              |
| 16     | Late August       | NST Sho                       | OP     | 1,200 m    | Dirt      | Niigata     | 350             | 2,200          |                                                                                              |
| 16     | Late August       | Toki Stakes                   | OP     | 1,400 m    | Turf      | Niigata     | 350             | 2,500          |                                                                                              |
| 16     | Late August       | Cluster Cup                   | G3     | 1,200 m    | Dirt      | Morioka     | 1,000           | 2,300          | added by `pre_nar`                                                                           |
| 16     | Late August       | Keeneland Cup                 | G3     | 1,200 m    | Turf      | Sapporo     | 1,500           | 4,100          |                                                                                              |
| 16     | Late August       | Kitakyushu Kinen              | G3     | 1,200 m    | Turf      | Kokura      | 1,250           | 3,900          |                                                                                              |
| 16     | Late August       | Sapporo Kinen                 | G2     | 2,000 m    | Turf      | Sapporo     | 2,000           | 7,000          |                                                                                              |
| 17     | Early September   | Enif Stakes                   | OP     | 1,400 m    | Dirt      | Hanshin     | 350             | 2,300          |                                                                                              |
| 17     | Early September   | Radio Nippon Sho              | OP     | 1,800 m    | Dirt      | Nakayama    | 350             | 2,200          |                                                                                              |
| 17     | Early September   | Tancho Stakes                 | OP     | 2,600 m    | Turf      | Sapporo     | 350             | 2,400          |                                                                                              |
| 17     | Early September   | Keisei Hai Autumn Handicap    | G3     | 1,600 m    | Turf      | Nakayama    | 1,250           | 3,900          |                                                                                              |
| 17     | Early September   | Niigata Kinen                 | G3     | 2,000 m    | Turf      | Niigata     | 1,500           | 4,100          |                                                                                              |
| 17     | Early September   | Centaur Stakes                | G2     | 1,200 m    | Turf      | Hanshin     | 1,900           | 5,900          |                                                                                              |
| 17     | Early September   | Prix Foy                      | G2     | 2,400 m    | Turf      | Longchamp   | 2,000           | ⚠️             | **not on Global**; added by `pre_2_5th_anni`; payout curve `56` absent from `en/race-fans`   |
| 18     | Late September    | Nagatsuki Stakes              | OP     | 1,200 m    | Dirt      | Nakayama    | 350             | 2,200          |                                                                                              |
| 18     | Late September    | Port Island Stakes            | OP     | 1,600 m    | Turf      | Hanshin     | 350             | 2,500          |                                                                                              |
| 18     | Late September    | Sirius Stakes                 | G3     | 2,000 m    | Dirt      | Hanshin     | 1,000           | 3,600          |                                                                                              |
| 18     | Late September    | All Comers                    | G2     | 2,200 m    | Turf      | Nakayama    | 2,000           | 6,700          |                                                                                              |
| 18     | Late September    | Sazanka TV Hai                | G2     | 1,800 m    | Dirt      | Funabashi   | 1,800           | ⚠️             | added by `pre_nar`; payout curve `51` absent from `en/race-fans`                             |
| 18     | Late September    | Sprinters Stakes              | G1     | 1,200 m    | Turf      | Nakayama    | 15,000          | 13,000         |                                                                                              |
| 19     | Early October     | Green Channel Cup             | OP     | 1,400 m    | Dirt      | Tokyo       | 350             | 2,300          |                                                                                              |
| 19     | Early October     | October Stakes                | OP     | 2,000 m    | Turf      | Tokyo       | 350             | 2,600          |                                                                                              |
| 19     | Early October     | Opal Stakes                   | OP     | 1,200 m    | Turf      | Kyoto       | 350             | 2,500          |                                                                                              |
| 19     | Early October     | Shinetsu Stakes               | OP     | 1,400 m    | Turf      | Niigata     | 350             | 2,500          |                                                                                              |
| 19     | Early October     | Uzumasa Stakes                | OP     | 1,800 m    | Dirt      | Kyoto       | 350             | 2,200          |                                                                                              |
| 19     | Early October     | Fuchu Umamusume Stakes        | G2     | 1,800 m    | Turf      | Tokyo       | 1,800           | 5,500          |                                                                                              |
| 19     | Early October     | Kyoto Daishoten               | G2     | 2,400 m    | Turf      | Kyoto       | 2,000           | 6,700          |                                                                                              |
| 19     | Early October     | Ladies' Prelude               | G2     | 1,800 m    | Dirt      | Ooi         | 1,800           | 3,100          | added by `pre_nar`                                                                           |
| 19     | Early October     | Mainichi Okan                 | G2     | 1,800 m    | Turf      | Tokyo       | 2,000           | 6,700          |                                                                                              |
| 19     | Early October     | Tokyo Hai                     | G2     | 1,200 m    | Dirt      | Ooi         | 1,800           | 3,500          | added by `pre_nar`                                                                           |
| 19     | Early October     | M.C. Nambu Hai                | G1     | 1,600 m    | Dirt      | Morioka     | 12,000          | 6,000          | added by `pre_nar`                                                                           |
| 20     | Late October      | Brazil Cup                    | OP     | 2,100 m    | Dirt      | Tokyo       | 350             | 2,300          |                                                                                              |
| 20     | Late October      | Cassiopeia Stakes             | OP     | 1,800 m    | Turf      | Kyoto       | 350             | 2,600          |                                                                                              |
| 20     | Late October      | Lumiere Autumn Dash           | OP     | 1,000 m    | Turf      | Niigata     | 350             | 2,500          |                                                                                              |
| 20     | Late October      | Muromachi Stakes              | OP     | 1,200 m    | Dirt      | Kyoto       | 350             | 2,200          |                                                                                              |
| 20     | Late October      | Fuji Stakes                   | G2     | 1,600 m    | Turf      | Tokyo       | 1,900           | 5,900          |                                                                                              |
| 20     | Late October      | Swan Stakes                   | G2     | 1,400 m    | Turf      | Kyoto       | 1,900           | 5,900          |                                                                                              |
| 20     | Late October      | Tenno Sho (Autumn)            | G1     | 2,000 m    | Turf      | Tokyo       | 20,000          | 15,000         |                                                                                              |
| 21     | Early November    | Oro Cup                       | OP     | 1,400 m    | Turf      | Tokyo       | 350             | 2,500          |                                                                                              |
| 21     | Early November    | Fukushima Kinen               | G3     | 2,000 m    | Turf      | Fukushima   | 1,500           | 4,100          |                                                                                              |
| 21     | Early November    | Miyako Stakes                 | G3     | 1,800 m    | Dirt      | Kyoto       | 750             | 3,800          |                                                                                              |
| 21     | Early November    | Musashino Stakes              | G3     | 1,600 m    | Dirt      | Tokyo       | 1,250           | 3,800          |                                                                                              |
| 21     | Early November    | Copa Republica Argentina      | G2     | 2,500 m    | Turf      | Tokyo       | 1,900           | 5,700          |                                                                                              |
| 21     | Early November    | JBC Classic                   | G1     | 2,000 m    | Dirt      | Ooi         | 12,000          | 8,000          |                                                                                              |
| 21     | Early November    | JBC Ladies’ Classic           | G1     | 1,800 m    | Dirt      | Ooi         | 12,000          | 4,100          |                                                                                              |
| 21     | Early November    | JBC Sprint                    | G1     | 1,200 m    | Dirt      | Ooi         | 12,000          | 6,000          |                                                                                              |
| 21     | Early November    | Queen Elizabeth II Cup        | G1     | 2,200 m    | Turf      | Kyoto       | 10,000          | 10,500         |                                                                                              |
| 22     | Late November     | Andromeda Stakes              | OP     | 2,000 m    | Turf      | Kyoto       | 350             | 2,600          |                                                                                              |
| 22     | Late November     | Autumn Leaf Stakes            | OP     | 1,200 m    | Dirt      | Kyoto       | 350             | 2,200          |                                                                                              |
| 22     | Late November     | Capital Stakes                | OP     | 1,600 m    | Turf      | Tokyo       | 350             | 2,500          |                                                                                              |
| 22     | Late November     | Fukushima Minyu Cup           | OP     | 1,700 m    | Dirt      | Fukushima   | 350             | 2,300          |                                                                                              |
| 22     | Late November     | Shimotsuki Stakes             | OP     | 1,400 m    | Dirt      | Tokyo       | 350             | 2,200          |                                                                                              |
| 22     | Late November     | Keihan Hai                    | G3     | 1,200 m    | Turf      | Kyoto       | 1,250           | 3,900          |                                                                                              |
| 22     | Late November     | Japan Cup                     | G1     | 2,400 m    | Turf      | Tokyo       | 25,000          | 30,000         |                                                                                              |
| 22     | Late November     | Mile Championship             | G1     | 1,600 m    | Turf      | Kyoto       | 15,000          | 11,000         |                                                                                              |
| 23     | Early December    | December Stakes               | OP     | 1,800 m    | Turf      | Nakayama    | 350             | 2,600          |                                                                                              |
| 23     | Early December    | Lapis Lazuli Stakes           | OP     | 1,200 m    | Turf      | Nakayama    | 350             | 2,500          |                                                                                              |
| 23     | Early December    | Rigel Stakes                  | OP     | 1,600 m    | Turf      | Hanshin     | 350             | 2,500          |                                                                                              |
| 23     | Early December    | Shiwasu Stakes                | OP     | 1,800 m    | Dirt      | Nakayama    | 350             | 2,300          |                                                                                              |
| 23     | Early December    | Tanzanite Stakes              | OP     | 1,200 m    | Turf      | Hanshin     | 350             | 2,300          |                                                                                              |
| 23     | Early December    | Capella Stakes                | G3     | 1,200 m    | Dirt      | Nakayama    | 1,000           | 3,600          |                                                                                              |
| 23     | Early December    | Challenge Cup                 | G3     | 2,000 m    | Turf      | Hanshin     | 1,500           | 4,100          |                                                                                              |
| 23     | Early December    | Chunichi Shimbun Hai          | G3     | 2,000 m    | Turf      | Chukyo      | 1,500           | 4,100          |                                                                                              |
| 23     | Early December    | Queen Sho                     | G3     | 1,800 m    | Dirt      | Funabashi   | 1,000           | ⚠️             | added by `pre_nar`; payout curve `53` absent from `en/race-fans`                             |
| 23     | Early December    | Turquoise Stakes              | G3     | 1,600 m    | Turf      | Nakayama    | 1,000           | 3,600          |                                                                                              |
| 23     | Early December    | Stayers Stakes                | G2     | 3,600 m    | Turf      | Nakayama    | 2,000           | 6,200          |                                                                                              |
| 23     | Early December    | Champions Cup                 | G1     | 1,800 m    | Dirt      | Chukyo      | 12,000          | 10,000         |                                                                                              |
| 24     | Late December     | Betelgeuse Stakes             | OP     | 1,800 m    | Dirt      | Hanshin     | 350             | 2,200          |                                                                                              |
| 24     | Late December     | Galaxy Stakes                 | OP     | 1,400 m    | Dirt      | Hanshin     | 350             | 2,200          |                                                                                              |
| 24     | Late December     | Hanshin Cup                   | G2     | 1,400 m    | Turf      | Hanshin     | 2,000           | 6,700          |                                                                                              |
| 24     | Late December     | Arima Kinen                   | G1     | 2,500 m    | Turf      | Nakayama    | 25,000          | 30,000         |                                                                                              |
| 24     | Late December     | Tokyo Daishoten               | G1     | 2,000 m    | Dirt      | Ooi         | 12,000          | 8,000          |                                                                                              |

@@Special races — 9 rows

| Turn   | Slot               | Race                            | Tier     | Distance   | Surface   | Track   | Fans to enter   | Fans for 1st   | Notes                                                                                  |
| ------ | ------------------ | ------------------------------- | -------- | ---------- | --------- | ------- | --------------- | -------------- | -------------------------------------------------------------------------------------- |
| 12     | Late June          | Junior Make Debut               | Debut    | -          | flexes    | -       | -               | -              | distance/surface flex with your most-run types; mandatory                              |
| 13     | Early July         | Junior Maiden Race              | Maiden   | -          | flexes    | -       | -               | -              | distance/surface flex with your most-run types; conditional                            |
| -      | after Senior Dec   | Grand Masters                   | G1       | -          | flexes    | -       | -               | -              | added by `pre_2nd_anni`; distance/surface flex with your most-run types; conditional   |
| -      | after Senior Dec   | Twinkle Star Climax             | G1       | -          | flexes    | -       | -               | -              | added by `pre_mant`; distance/surface flex with your most-run types; conditional       |
| -      | after Senior Dec   | URA Finals Final (Aoharu)       | G1       | -          | flexes    | -       | -               | -              | added by `pre_aoharu`; distance/surface flex with your most-run types; conditional     |
| -      | after Senior Dec   | URA Finals Final (Grand Live)   | G1       | -          | flexes    | -       | -               | -              | added by `pre_gl`; distance/surface flex with your most-run types; conditional         |
| -      | after Senior Dec   | URA Finals Final (URA)          | G1       | -          | flexes    | -       | -               | -              | distance/surface flex with your most-run types; conditional                            |
| -      | after Senior Dec   | URA Finals Qualifier            | G1       | -          | flexes    | -       | -               | -              | distance/surface flex with your most-run types; conditional                            |
| -      | after Senior Dec   | URA Finals Semifinal            | G1       | -          | flexes    | -       | -               | -              | distance/surface flex with your most-run types; conditional                            |

@@G1 index — 48 rows

| Year      | Turn   | Slot             | Race                            | Tier   | Distance   | Surface   | Track       | Fans to enter   | Fans for 1st   | Notes                                                              |
| --------- | ------ | ---------------- | ------------------------------- | ------ | ---------- | --------- | ----------- | --------------- | -------------- | ------------------------------------------------------------------ |
| Junior    | 23     | Early December   | Asahi Hai Futurity Stakes       | G1     | 1,600 m    | Turf      | Hanshin     | 1,000           | 7,000          |                                                                    |
| Junior    | 23     | Early December   | Hanshin Juvenile Fillies        | G1     | 1,600 m    | Turf      | Hanshin     | 1,000           | 6,500          |                                                                    |
| Junior    | 24     | Late December    | Hopeful Stakes                  | G1     | 2,000 m    | Turf      | Nakayama    | 1,000           | 7,000          |                                                                    |
| Junior    | 24     | Late December    | Zen-Nippon Junior Yushun        | G1     | 1,600 m    | Dirt      | Kawasaki    | 1,000           | ⚠️             | added by `pre_nar`; payout curve `54` absent from `en/race-fans`   |
| Classic   | 7      | Early April      | Oka Sho                         | G1     | 1,600 m    | Turf      | Hanshin     | 4,500           | 10,500         |                                                                    |
| Classic   | 7      | Early April      | Satsuki Sho                     | G1     | 2,000 m    | Turf      | Nakayama    | 4,500           | 11,000         |                                                                    |
| Classic   | 9      | Early May        | NHK Mile Cup                    | G1     | 1,600 m    | Turf      | Tokyo       | 5,000           | 10,500         |                                                                    |
| Classic   | 10     | Late May         | Japanese Oaks                   | G1     | 2,400 m    | Turf      | Tokyo       | 6,000           | 11,000         |                                                                    |
| Classic   | 10     | Late May         | Tokyo Yushun (Japanese Derby)   | G1     | 2,400 m    | Turf      | Tokyo       | 6,000           | 20,000         |                                                                    |
| Classic   | 11     | Early June       | Yasuda Kinen                    | G1     | 1,600 m    | Turf      | Tokyo       | 15,000          | 13,000         |                                                                    |
| Classic   | 12     | Late June        | Takarazuka Kinen                | G1     | 2,200 m    | Turf      | Hanshin     | 20,000          | 15,000         |                                                                    |
| Classic   | 13     | Early July       | Japan Dirt Derby                | G1     | 2,000 m    | Dirt      | Ooi         | 4,000           | 4,500          |                                                                    |
| Classic   | 18     | Late September   | Sprinters Stakes                | G1     | 1,200 m    | Turf      | Nakayama    | 15,000          | 13,000         |                                                                    |
| Classic   | 19     | Early October    | M.C. Nambu Hai                  | G1     | 1,600 m    | Dirt      | Morioka     | 12,000          | 6,000          | added by `pre_nar`                                                 |
| Classic   | 20     | Late October     | Kikuka Sho                      | G1     | 3,000 m    | Turf      | Kyoto       | 7,500           | 12,000         |                                                                    |
| Classic   | 20     | Late October     | Shuka Sho                       | G1     | 2,000 m    | Turf      | Kyoto       | 7,500           | 10,000         |                                                                    |
| Classic   | 20     | Late October     | Tenno Sho (Autumn)              | G1     | 2,000 m    | Turf      | Tokyo       | 20,000          | 15,000         |                                                                    |
| Classic   | 21     | Early November   | JBC Classic                     | G1     | 2,000 m    | Dirt      | Ooi         | 12,000          | 8,000          |                                                                    |
| Classic   | 21     | Early November   | JBC Ladies’ Classic             | G1     | 1,800 m    | Dirt      | Ooi         | 12,000          | 4,100          |                                                                    |
| Classic   | 21     | Early November   | JBC Sprint                      | G1     | 1,200 m    | Dirt      | Ooi         | 12,000          | 6,000          |                                                                    |
| Classic   | 21     | Early November   | Queen Elizabeth II Cup          | G1     | 2,200 m    | Turf      | Kyoto       | 10,000          | 10,500         |                                                                    |
| Classic   | 22     | Late November    | Japan Cup                       | G1     | 2,400 m    | Turf      | Tokyo       | 25,000          | 30,000         |                                                                    |
| Classic   | 22     | Late November    | Mile Championship               | G1     | 1,600 m    | Turf      | Kyoto       | 15,000          | 11,000         |                                                                    |
| Classic   | 23     | Early December   | Champions Cup                   | G1     | 1,800 m    | Dirt      | Chukyo      | 12,000          | 10,000         |                                                                    |
| Classic   | 24     | Late December    | Arima Kinen                     | G1     | 2,500 m    | Turf      | Nakayama    | 25,000          | 30,000         |                                                                    |
| Classic   | 24     | Late December    | Tokyo Daishoten                 | G1     | 2,000 m    | Dirt      | Ooi         | 12,000          | 8,000          |                                                                    |
| Senior    | 3      | Early February   | Kawasaki Kinen                  | G1     | 2,100 m    | Dirt      | Kawasaki    | 12,000          | 6,000          | added by `pre_nar`                                                 |
| Senior    | 4      | Late February    | February Stakes                 | G1     | 1,600 m    | Dirt      | Tokyo       | 12,000          | 10,000         |                                                                    |
| Senior    | 6      | Late March       | Osaka Hai                       | G1     | 2,000 m    | Turf      | Hanshin     | 20,000          | 13,500         |                                                                    |
| Senior    | 6      | Late March       | Takamatsunomiya Kinen           | G1     | 1,200 m    | Turf      | Chukyo      | 15,000          | 13,000         |                                                                    |
| Senior    | 8      | Late April       | Tenno Sho (Spring)              | G1     | 3,200 m    | Turf      | Kyoto       | 20,000          | 15,000         |                                                                    |
| Senior    | 9      | Early May        | Kashiwa Kinen                   | G1     | 1,600 m    | Dirt      | Funabashi   | 12,000          | 8,000          | added by `pre_nar`                                                 |
| Senior    | 9      | Early May        | Victoria Mile                   | G1     | 1,600 m    | Turf      | Tokyo       | 10,000          | 10,500         |                                                                    |
| Senior    | 11     | Early June       | Yasuda Kinen                    | G1     | 1,600 m    | Turf      | Tokyo       | 15,000          | 13,000         |                                                                    |
| Senior    | 12     | Late June        | Takarazuka Kinen                | G1     | 2,200 m    | Turf      | Hanshin     | 20,000          | 15,000         |                                                                    |
| Senior    | 12     | Late June        | Teio Sho                        | G1     | 2,000 m    | Dirt      | Ooi         | 12,000          | 6,000          |                                                                    |
| Senior    | 18     | Late September   | Sprinters Stakes                | G1     | 1,200 m    | Turf      | Nakayama    | 15,000          | 13,000         |                                                                    |
| Senior    | 19     | Early October    | M.C. Nambu Hai                  | G1     | 1,600 m    | Dirt      | Morioka     | 12,000          | 6,000          | added by `pre_nar`                                                 |
| Senior    | 20     | Late October     | Tenno Sho (Autumn)              | G1     | 2,000 m    | Turf      | Tokyo       | 20,000          | 15,000         |                                                                    |
| Senior    | 21     | Early November   | JBC Classic                     | G1     | 2,000 m    | Dirt      | Ooi         | 12,000          | 8,000          |                                                                    |
| Senior    | 21     | Early November   | JBC Ladies’ Classic             | G1     | 1,800 m    | Dirt      | Ooi         | 12,000          | 4,100          |                                                                    |
| Senior    | 21     | Early November   | JBC Sprint                      | G1     | 1,200 m    | Dirt      | Ooi         | 12,000          | 6,000          |                                                                    |
| Senior    | 21     | Early November   | Queen Elizabeth II Cup          | G1     | 2,200 m    | Turf      | Kyoto       | 10,000          | 10,500         |                                                                    |
| Senior    | 22     | Late November    | Japan Cup                       | G1     | 2,400 m    | Turf      | Tokyo       | 25,000          | 30,000         |                                                                    |
| Senior    | 22     | Late November    | Mile Championship               | G1     | 1,600 m    | Turf      | Kyoto       | 15,000          | 11,000         |                                                                    |
| Senior    | 23     | Early December   | Champions Cup                   | G1     | 1,800 m    | Dirt      | Chukyo      | 12,000          | 10,000         |                                                                    |
| Senior    | 24     | Late December    | Arima Kinen                     | G1     | 2,500 m    | Turf      | Nakayama    | 25,000          | 30,000         |                                                                    |
| Senior    | 24     | Late December    | Tokyo Daishoten                 | G1     | 2,000 m    | Dirt      | Ooi         | 12,000          | 8,000          |                                                                    |

@@Counts — slots

| Year      | G1    | G2    | G3    | OP    | Pre-OP   | Maiden   | Debut   | total   |
| --------- | ----- | ----- | ----- | ----- | -------- | -------- | ------- | ------- |
| Junior    | 4     | 2     | 9     | 15    | 26       | 1        | 1       | 58      |
| Classic   | 22    | 27    | 47    | 66    | 0        | 0        | 0       | 162     |
| Senior    | 22    | 29    | 52    | 83    | 0        | 0        | 0       | 186     |
| Finale    | 7     | 0     | 0     | 0     | 0        | 0        | 0       | 7       |

@@Unique races, base pool vs additions

| Tier     | Sprint   | Mile   | Medium   | Long   | flexes   | total   |
| -------- | -------- | ------ | -------- | ------ | -------- | ------- |
| G1       | 3        | 10     | 14       | 3      | 3        | 33      |
| G2       | 6        | 12     | 13       | 5      | 0        | 36      |
| G3       | 18       | 33     | 17       | 1      | 0        | 69      |
| OP       | 51       | 48     | 16       | 3      | 0        | 118     |
| Pre-OP   | 9        | 12     | 5        | 0      | 0        | 26      |
| G1       | 3        | 13     | 15       | 3      | 7        | 41      |
| G2       | 7        | 14     | 16       | 5      | 0        | 42      |
| G3       | 20       | 37     | 18       | 1      | 0        | 76      |
| OP       | 51       | 48     | 16       | 3      | 0        | 118     |
| Pre-OP   | 9        | 12     | 5        | 0      | 0        | 26      |

  base pool        G1: 33  G2: 36  G3: 69
  with additions   G1: 41  G2: 42  G3: 76

@@Global exclusions

| Race                                        | Tier   | Distance   | Track              | In a career slot?   | Flag                                |
| ------------------------------------------- | ------ | ---------- | ------------------ | ------------------- | ----------------------------------- |
| Tenno Sho (Autumn)                          | G1     | 2,000 m    | Nakayama           | no                  | `unreleased_servers: ['en']`        |
| Mile Championship Nambu Hai                 | G1     | 1,600 m    | Tokyo              | no                  | `unreleased_servers: ['en']`        |
| Japan Cup                                   | G1     | 2,200 m    | Nakayama           | no                  | `unreleased_servers: ['en']`        |
| Sprinters Stakes                            | G1     | 1,200 m    | Niigata            | no                  | `unreleased_servers: ['en']`        |
| Mile Championship                           | G1     | 1,600 m    | Hanshin            | no                  | `unreleased_servers: ['en']`        |
| Queen Elizabeth Cup                         | G1     | 2,200 m    | Hanshin            | no                  | `unreleased_servers: ['en']`        |
| Kawasaki Kinen                              | G1     | 2,100 m    | Kawasaki           | yes: 1106           | `did_not_exist: 'pre_nar'`          |
| Zen-Nippon Junior Yushun                    | G1     | 1,600 m    | Kawasaki           | yes: 1107           | `did_not_exist: 'pre_nar'`          |
| Kashiwa Kinen                               | G1     | 1,600 m    | Funabashi          | yes: 1108           | `did_not_exist: 'pre_nar'`          |
| M.C. Nambu Hai                              | G1     | 1,600 m    | Morioka            | yes: 1109,1109_2    | `did_not_exist: 'pre_nar'`          |
| Prix de l'Arc de Triomphe                   | G1     | 2,400 m    | Longchamp          | no                  | `did_not_exist: 'pre_2_5th_anni'`   |
| Ladies' Prelude                             | G2     | 1,800 m    | Ooi                | yes: 1119,1119_2    | `did_not_exist: 'pre_nar'`          |
| Tokyo Hai                                   | G2     | 1,200 m    | Ooi                | yes: 1120,1120_2    | `did_not_exist: 'pre_nar'`          |
| Empress Hai                                 | G2     | 2,100 m    | Kawasaki           | yes: 1121           | `did_not_exist: 'pre_nar'`          |
| Kanto Oaks                                  | G2     | 2,100 m    | Kawasaki           | yes: 1122           | `did_not_exist: 'pre_nar'`          |
| Diolite Kinen                               | G2     | 2,400 m    | Funabashi          | yes: 1123           | `did_not_exist: 'pre_nar'`          |
| Sazanka TV Hai                              | G2     | 1,800 m    | Funabashi          | yes: 1124,1124_2    | `did_not_exist: 'pre_nar'`          |
| TCK Jo-o Hai                                | G3     | 1,800 m    | Ooi                | yes: 1125           | `did_not_exist: 'pre_nar'`          |
| Tokyo Sprint                                | G3     | 1,200 m    | Ooi                | yes: 1126           | `did_not_exist: 'pre_nar'`          |
| Sparking Lady Cup                           | G3     | 1,600 m    | Kawasaki           | yes: 1127,1127_2    | `did_not_exist: 'pre_nar'`          |
| Marine Cup                                  | G3     | 1,600 m    | Funabashi          | yes: 1128,1128_2    | `did_not_exist: 'pre_nar'`          |
| Queen Sho                                   | G3     | 1,800 m    | Funabashi          | yes: 1129,1129_2    | `did_not_exist: 'pre_nar'`          |
| Mercury Cup                                 | G3     | 2,000 m    | Morioka            | yes: 1130,1130_2    | `did_not_exist: 'pre_nar'`          |
| Cluster Cup                                 | G3     | 1,200 m    | Morioka            | yes: 1131,1131_2    | `did_not_exist: 'pre_nar'`          |
| Prix Niel                                   | G2     | 2,400 m    | Longchamp          | yes: 2905           | `unreleased_servers: ['en']`        |
| Prix Foy                                    | G2     | 2,400 m    | Longchamp          | yes: 2907           | `unreleased_servers: ['en']`        |
| Prix de l'Arc de Triomphe                   | G1     | 2,400 m    | Longchamp          | no                  | `unreleased_servers: ['en']`        |
| Prix de l'Arc de Triomphe                   | G1     | 2,400 m    | Longchamp          | no                  | `unreleased_servers: ['en']`        |
| Prix de l'Arc de Triomphe                   | G1     | 2,400 m    | Longchamp          | no                  | `unreleased_servers: ['en']`        |
| American Oaks                               | G1     | 2,000 m    | Santa Anita Park   | no                  | `unreleased_servers: ['en']`        |
| Hanshin Umamusume Stakes (Story Race Use)   | G2     | 1,400 m    | Hanshin            | no                  | `unreleased_servers: ['en']`        |

---

## [Rosy_Dreams]Rice_Shower_Unity-Cup.md

> **Augmented 2026-10-10 (Round 14, augment-only).** Source: `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` (776 lines), reproduced verbatim below with headings demoted one level. The source file remains in place; no citation was repointed and nothing was deleted.

## Unity Cup run report: [Rosy Dreams] Rice Shower

**Evidence base:** 68 `[Global]` client frames captured 2026-10-03: 19 at 16:09, 19 at 16:27, 20 at 17:07
and 10 at 17:53. One pair from the 17:07 set is byte-identical, so 67 distinct. Frame references are the
first eight characters of each capture filename. The deck's effect sets and skill lists come from a
second source: the seeded catalog behind `/support-cards`, rows 30003, 30016, 30018, 30019, 30020 and
30030. This revision supersedes the first-pass version of this file, which read only the 16:09 set.

**Research folded in:** the two online passes (Spirit Burst counting, cap composition) returned on
2026-10-03. Their results are in §1.2, §2.2, §2.4 and §6, with publisher dates and the repo's ⚠️ STALE
mark on anything older than 90 days.

---

### 1. What the screenshots establish

#### 1.1 Run identity and moment

Scenario Unity Cup, confirmed by the logo panel "Unity Cup: Shine On, Team Spirit!" (`885d4671`).
Trainee Rice Shower, `[Rosy Dreams]` card, ★3, Potential Lvl 2 (`1dde6b2f`). Turn: Senior Year Early
Oct, **5 turns left, 5 turns until the Unity Cup** (`297b931b`). Fans 209,245, class Star; the ladder
puts Top Star at 240,000, so **30,755 fans** is the open numeric target (`7a68aedc`).

#### 1.2 Live stat line and the caps above it

| Stat    | Value | Rank | Cap shown | Growth | Over the 1300 floor   |  |
| ------- | ----- | ---- | --------- | ------ | --------------------- |  |
| Speed   | 607   | B    | 1325      | +0%    | +25                   |  |
| Stamina | 577   | C+   | 1322      | +10%   | +22                   |  |
| Power   | 549   | C+   | 1368      | +0%    | +68                   |  |
| Guts    | 736   | B+   | 1308      | +20%   | +8                    |  |
| Wit     | 397   | D+   | 1800      | +0%    | N/A (different floor) |  |

Skill Points: **173 unspent** (`09e754d6`). The 1300 / 1300 / 1300 / 1300 / 1800 floor is the scenario
base: the 2026-07 rework added +100 to Speed, Stamina, Power and Guts and +600 to Wit ([game8.co
545572, 2026-07-07](https://game8.co/games/Umamusume-Pretty-Derby/archives/545572)), and above 1200 a
stat point buys less race performance than below it.

The four extras over that floor were attributed in the first version of this file to inheritance
breakthroughs, which run **★1 +4 / ★2 +9 / ★3 +16 per activation** at each of three inheritance moments
(career start, Classic April, Senior April; [gamewith, ⚠️ STALE: 2023-06-28](https://gamewith.jp/uma-musume/article/show/360428),
corroborated by [game8.jp, ⚠️ STALE: 2025-11-21](https://game8.jp/umamusume/475668)). **The ancestry
panels in §1.5 falsify that attribution.** The tree holds six blue sparks: Stamina ★★★, Power ★★★,
Speed ★★☆, Speed ★★☆, Guts ★☆☆, Power ★☆☆. Only the Guts figure survives, at +4 × 2 = 8. Speed +25 is
not a sum of 9s, Stamina +22 is not a sum of 16s, and Power +68 needs four activations of a ★★★ in a run
that has three inheritance events. At least one further cap layer is contributing and none of it is
visible in these frames.

The support-card layer is the obvious candidate and it is the one that does not hold up: ids 20 to 24
are real client Max-Stat effect slots under the Global names Max Speed through Max Wit
([umamusu.wiki support-effects module, 2026-09-17](https://umamusu.wiki/Module:Game/Supports/Data/Effects)),
but no publisher page in the research pass carries a per-level magnitude for any Global card, and the
seeded catalog's effect arrays for these six cards hold no Max-Stat entry at all. Bursts raising **team
members'** caps is documented with no magnitude
([kamigame, ⚠️ STALE: 2024-04-10](https://kamigame.jp/umamusume/page/172795448925364187.html)). The gap
stays open, now as a measured shortfall rather than an unexamined number.

Aptitudes (`1dde6b2f`): Turf A, Dirt G. Sprint E, Mile B, Medium A, Long A. Front A, Pace A, Late B,
End G. A Medium/Long build with the pace half available, and Guts is the designed carry stat at +20%.

*Dated erratum 2026-10-09 (D8 of the UX walk remediation). The sentence above stands as written, and
three of its letters do not agree with the tool's committed catalogue source. The GameTora export the
seeder reads (`database/seeders/data/gametora-characters.e9e9ee6d.json`, card `103001`, `[Rosy
Dreams]`) carries `aptitude: ["A","G","E","C","A","A","B","A","C","G"]` in the order turf, dirt,
sprint, mile, medium, long, front runner, pace chaser, late surger, end closer — Mile **C**, Front
Runner **B**, Late Surger **C**, where this document records B, A and B. Measured 2026-10-09: the
seeded column and the trainee picker both reproduce the export letter for letter, so no seed,
transformation or display defect exists on the tool's side; the two readings disagree about the
client. Which one the game actually prints was not settled here, and the tool was not changed: the
picker's values are the catalogue's own (`CareerTraineeSelectTest` pins all ten). A reader comparing
the two should treat the difference as open, not as a bug in the tool.*

#### 1.3 Skills owned

The trainee's own Details screen, Skills tab (`1dde6b2f`), shows ten chips and the list scrolls:
Blue Rose Closer **Lvl 3** (her unique, gradient chip), Red Shift/LP1211-M, Corner Adept ○, Swinging
Maestro, Nimble Navigator, Steadfast, Deep Breaths, Extra Tank, Pace Chaser Straightaways ○, Shrewd
Step. The Learn screen additionally marks **Corner Recovery ○** as Obtained (`09e754d6`), so the set
runs past the ten visible chips.

#### 1.4 Deck, all six cards, all SSR

Identity read from the six Featured Cards panels; the full effect set and the skill lists come from the
seeded catalog behind `/support-cards` (`support_cards` rows 30003, 30018, 30020, 30016, 30030, 30019),
which carries each card's effects at their maximum anchors. The client popup shows only what the card
has reached at its current level and limit breaks, so the two columns below are different things: the
catalog is what the card can be, the client column is what this run has.

| Card                                   | Type    | Client level | Unique Perk                                                             | Scenario link |  |
| -------------------------------------- | ------- | ------------ | ----------------------------------------------------------------------- | ------------- |  |
| Tokai Teio [Dream Big!]                | Speed   | 35/35        | Dream Big! L30: Friendship Bonus and Initial Speed                      | no            |  |
| Nishino Flower [Even the Littlest Bud] | Speed   | 30/30        | Even the Littlest Bud L30: Mood Effect and Initial Friendship Gauge     | no            |  |
| Biko Pegasus [Double Carrot Punch!]    | Speed   | 30/30        | Double Carrot Punch! L30: Training Effectiveness and Specialty Priority | no            |  |
| Super Creek [Piece of Mind]            | Stamina | 50/50        | Piece of Mind L30: Friendship Bonus and Specialty Priority              | no            |  |
| Matikanetannhauser [Just Keep Going]   | Guts    | 30/30        | Just Keep Going L40: Friendship Bonus and Initial Guts                  | no            |  |
| Haru Urara [Urara's Day Off!]          | Guts    | 35/35        | Urara's Day Off! L40: Friendship Bonus and Race Bonus                   | **yes**       |  |

Three Speed, one Stamina, two Guts, no Wit, no Pal. The sixth slot is a **friend card**: the Career
Profile rail tags Super Creek's entry with a "Friends" pill where the other five carry their discipline
icon (`85e4c75a`), which is why the only 50/50 card in the deck is the one borrowed from another
account. The epithet on Matikanetannhauser carries no
trailing exclamation mark in either the catalog or the client; the first pass of this file printed one.
The scenario-link column is the application's own derivation, `SupportCard::isScenarioLink('unity_cup')`
against `config('scenarios.php')`, and it confirms the corpus citation this file previously leaned on:
Haru Urara is the deck's only Unity Cup link, so every Special Training and Burst instance she joins
carries the +1 (`02-unity-cup.md:78`). Super Creek at 50/50 is the only maxed card, and every client row
visible for her equals her catalog maximum, which is the check that the two columns mean what they say.

| Card               | Effect set, values at max (catalog)                                                                                                                                   | Client rows visible                                                                                                                |  |
| ------------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------- |  |
| Tokai Teio         | Friendship Bonus 20, Mood Effect 60, Power Bonus 1, Initial Friendship Gauge 25, Race Bonus 10, Fan Bonus 15, Hint Levels 2, Hint Frequency 40, Specialty Priority 35 | FB 16, Mood 45, Power Bonus 1, Hint Freq 33, Specialty Priority 25, Hint Levels (row cut), Race 45 locked, Fan 45 locked           |  |
| Nishino Flower     | FB 20, Mood 30, Speed Bonus 1, Training Effectiveness 10, Initial Speed 30, Initial Friendship Gauge 35, Race 5, Fan 15, Hint Levels 3, Hint Frequency 50             | FB 15, Mood 20, Training Eff 5, Hint Levels Lv2, Hint Freq 40, Speed Bonus 35 locked, Initial Speed 45 locked                      |  |
| Biko Pegasus       | FB 25, Speed Bonus 1, Training Eff 15, Initial Speed 35, Initial Friendship Gauge 30, Race 10, Fan 20, Specialty Priority 35                                          | FB 20, Training Eff 10, Initial Speed 25, Fan 15, Specialty Priority 20, Speed Bonus 35 locked, Initial Friendship Gauge 45 locked |  |
| Super Creek        | FB 25, Stamina Bonus 1, Training Eff 15, Initial Stamina 35, Initial Friendship Gauge 30, Race 10, Fan 20, Specialty Priority 35                                      | FB 25, Stamina Bonus 1, Training Eff 15, Initial Friendship Gauge 30, Race 10, Fan 20, Specialty Priority 35                       |  |
| Matikanetannhauser | FB 25, Mood 30, Guts Bonus 1, Training Eff 5, Initial Guts 35, Race 5, Fan 10, Specialty Priority 65                                                                  | FB 20, Training Eff 5, Initial Guts 25, Fan 5, Specialty Priority 50, Guts Bonus 35 locked, Mood 45 locked                         |  |
| Haru Urara         | FB 20, Mood 30, Training Eff 15, Initial Guts 35, Race 5, Fan 10, Specialty Priority 50, Skill Point Bonus 1                                                          | FB 16, Training Eff 11, Initial Guts 27, Fan 6, Specialty Priority 40, Skill Point Bonus 1, Mood 45 locked                         |  |

**Correction to the first pass:** the number beside a padlock in the client popup is the card level at
which that effect unlocks, not a value. "Race Bonus 🔒 Lvl 45" means the effect arrives at level 45,
which these cards cannot reach until their limit breaks raise the cap. It is not a 45% anything.

| Card               | Hint pool (catalog)                                                                                                     | Story-event skills (catalog)                      |  |
| ------------------ | ----------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------- |  |
| Tokai Teio         | Prudent Positioning, Nimble Navigator, Go with the Flow, Thunderbolt Step, Soft Step, Shrewd Step                       | Rushing Gale!, Pace Chaser Straightaways ○        |  |
| Nishino Flower     | Hanshin Racecourse ○, Standard Distance ○, Firm Conditions ○, Updrafters, Pace Chaser Corners ○                         | Beeline Burst, Straightaway Adept, Countermeasure |  |
| Biko Pegasus       | Outer Post Proficiency ○, Wait-and-See, Gap Closer, Productive Plan, Updrafters, Meticulous Measures, Stop Right There! | Sprint Straightaways ○, Plan X                    |  |
| Super Creek        | Firm Conditions ○, Corner Recovery ○, Ramp Up, Homestretch Haste, Hesitant Pace Chasers                                 | Swinging Maestro, Deep Breaths                    |  |
| Matikanetannhauser | Lay Low, Pace Strategy, Steadfast, Deep Breaths, Fighter                                                                | Unruffled, Calm in a Crowd, Subdued Front Runners |  |
| Haru Urara         | none stored                                                                                                             | Long Shot ○, Unruffled                            |  |

This closes the per-card Skills question from the catalog rather than from the frames, and it cross-
validates the Learn screen: Soft Step at `Lv Max`, Shrewd Step and Nimble Navigator Obtained (Teio);
Pace Chaser Corners ○ at Lv4 and Firm Conditions ○ at Lv1 (Flower); Gap Closer and Productive Plan at
Lv1 and Lv3 (Pegasus); Hesitant Pace Chasers at Lv2 and Corner Recovery ○ Obtained (Creek); Steadfast
and Deep Breaths Obtained (Tannhauser); Long Shot ○ at Lv1 and Unruffled at Lv4 (Urara, from her events,
since she stores no hint pool). Skills on the Learn screen with no hint and no discount, like Ramp Up,
Calm in a Crowd and Wait-and-See, are pool members whose level never got raised. That is the
"drawn from that support card's own hint pool" rule at `02-unity-cup.md:95` working as documented.

Each card's popup also carries a **"Unity Cup Exclusive"** block: the character's own state as a team
member, since the deck's characters join the team. Those six blocks are in §1.9.

#### 1.5 Inheritance, from the Sparks panels

The ancestry tree is now read directly, from the Legacy Umamusume panel (`2161e855`) and one Sparks
panel per ancestor (`fd78e996`, `31fd26c4`, `f7a6952f`, `e771563c`, `24f5b5d0`, `eea37f2a`):

```text
Rice Shower
├── Legacy 1  Vodka [Wild Top Gear]  rank B+
│     ├── Daiwa Scarlet [Peak Blue]            rank B+
│     └── Seiun Sky [Reeling in the Big One]   rank A
└── Legacy 2  Maruzensky [Formula R]  rank UG, flagged Guest
      ├── Daiwa Scarlet [Peak Blue]   rank UG
      └── Taiki Shuttle [Wild Frontier]  rank UG
```

| Ancestor                     | Blue            | Pink             | Green unique              | White sparks                                                                                                                                                                                                                                                       |  |
| ---------------------------- | --------------- | ---------------- | ------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |  |
| Vodka                        | Power ★☆☆       | Late Surger ★★☆  | Cut and Drive! ★★☆        | Oka Sho ★☆☆, Victoria Mile ★☆☆, Shuka Sho ★★☆, Japan C. ★★☆, Extra Tank ★★☆, Soft Step ★★★                                                                                                                                                                         |  |
| Daiwa Scarlet, Legacy 1 side | Guts ★☆☆        | Mile ★☆☆         | Resplendent Red Ace ★☆☆   | Osaka Hai ★★☆, Tenno Sho (Autumn) ★☆☆, Competitive Spirit ○ ★★★, Pace Chaser Straightaways ○ ★★☆                                                                                                                                                                   |  |
| Seiun Sky                    | **Stamina ★★★** | Turf ★★☆         | Angling and Scheming ★★☆  | Kikuka Sho ★☆☆, Corner Recovery ○ ★★☆, URA Finale ★☆☆                                                                                                                                                                                                              |  |
| Maruzensky                   | Speed ★★☆       | Turf ★★☆         | Red Shift/LP1211-M ★☆☆    | Takarazuka Kinen ★★☆, Asahi Hai F.S. ★★☆, Corner Adept ○ ★★☆, Ramp Up ★★☆, Mile Straightaways ○ ★☆☆, **Ignited Spirit: Guts +** ★☆☆                                                                                                                                |  |
| Daiwa Scarlet, Legacy 2 side | Speed ★★☆       | Front Runner ★★☆ | Resplendent Red Ace ★☆☆   | Osaka Hai ★★☆, Tenno Sho (Spring) ★★☆, NHK Mile C. ★★☆, Japan C. ★★☆, Corner Adept ○ ★★☆, Unyielding Spirit ★★☆, Front Runner Savvy ○ ★☆☆                                                                                                                          |  |
| Taiki Shuttle                | **Power ★★★**   | Pace Chaser ★★☆  | Shooting for Victory! ★★☆ | NHK Mile C. ★★☆, Sprinters S. ★☆☆, JBC L. Classic ★★☆, Tokyo Daishoten ★★☆, Corner Adept ○ ★★☆, Corner Acceleration ○ ★★☆, Shifting Gears ★☆☆, Productive Plan ★★☆, Pace Chaser Straightaways ○ ★★★, Disorient ★★☆, Sympathy ★★☆, Head-On ★★☆, Glittering Star ★★☆ |  |

**These panels replace the star reads in the first version of this section, and three of them were
wrong.** Reading stars off the scrolling Sparks grid gave Shooting for Victory! ★★★ (it is ★★☆),
Resplendent Red Ace ★★☆ (★☆☆ in both branches), and a Guts ★★★ that does not exist; the Guts spark is
Daiwa Scarlet's ★☆☆. The only ★★★ blue sparks are Seiun Sky's Stamina and Taiki Shuttle's Power, and
there is **no Wit spark anywhere in the tree**, which is the clearest explanation available for Wit
sitting at 397. The consequence for the cap arithmetic in §1.2 is in that section.

Two details worth keeping: the same card, [Peak Blue] Daiwa Scarlet, appears on both branches with
different spark sets, so sparks are per-ancestor-instance rather than per-card, and Legacy 2 is flagged
**Guest** at rank UG while Legacy 1 sits at B+ and A. Rank badges here are the ancestor's own end-of-run
class, not a spark grade.

#### 1.6 Unity Cup state

Team **Blue Bloom**, motto "Dreaming Big", **Team Rank S already reached**, league placement **8th**
(`eccdc650`). All four preseason rounds won: Round 1 after Junior Late Dec, Round 2 after Classic Late
Jun, Round 3 after Classic Late Dec, Round 4 after Senior Late Jun. **Finals next**, after Senior Late
Dec, five turns away.

Goals (`885d4671`): cleared are Place top 3 in Nikkei Sho (Senior Late Mar), Place 1st in Tenno Sho
(Spring) (Senior Late Apr), Place top 3 in Takarazuka Kinen (Senior Late Jun). Active: **Place 1st in
Arima Kinen**, Senior Late Dec, entry criteria met, 5 turns.

Race calendar (`9d0a0dfb`): the Senior board is empty except **Kyoto Daishoten** scheduled Early Oct
and the **Arima Kinen** goal slot at Late Dec.

**Race day, Senior Early Oct** (`85e4c75a`, `f49bd7b1`, `714f495d`). The turn offers two races and the
client marks the first one Recommended:

| Option                      | Grade | Course                                                  | Fans on a win | Her fit          |  |
| --------------------------- | ----- | ------------------------------------------------------- | ------------- | ---------------- |  |
| Kyoto Daishoten             | G2    | Kyoto Turf 2400m (Medium) Right / Outer, Fall, **Soft** | 6,700         | Turf A, Medium A |  |
| Mile Championship Nambu Hai | G1    | Morioka **Dirt** 1600m (Mile) Left                      | 6,000         | Dirt G, Mile B   |  |

The Daishoten card: Full Gate 18, held in Classic Year / Senior Year Oct, entry criteria "2,000 fans or
more" met at 209,245, and fan rewards of 6,700 / 2,680 / 1,675 / 1,005 / 670 for first through fifth,
plus item and bonus rows with the note that carats can be obtained up to 20 times per day. The
Predictions dialog shows five target rings across the stats and the trainer's verdict: "Your trainee
seems pumped up and ready for a challenge. I'd say she could be a **top contender**."

Three consequences, each of which changes a line in §2.3. The higher-grade alternative is a Dirt race
and she is Dirt G, so the G1 is the worse ride. The going is **Soft**, so a Firm Conditions skill does
not fire this turn while the sun icon makes a Sunny Days skill the one that would. And "top contender"
argues against Long Shot ○, whose condition is 4th favourite or below; the Log is still empty, so that
is the client's own estimate rather than a starting-grid reading.

The fan ladder also puts the class target in reach: 30,755 fans to Top Star, 6,700 from this G2 alone,
with the Arima Kinen and the finals still to run. The G1's payout is not in these frames, so the target
is plausible rather than assured.

#### 1.7 Mood, Energy, and the last observed training turn

Mood is **GREAT**. The client's own Mood Effect panel is in the set (`bab6c92f`) and reads GREAT +20%
training / +4% running, GOOD +10/+2, NORMAL 0, BAD −10/−2, AWFUL −20/−4. That matches
`docs/UMAMUSUME_REFERENCE.md` §1.1.6 line for line, so it re-confirms the race-side figure this repo
settled once already against the wikis.

Energy is low: the bar sits near a third, and the game is printing the warning "Your trainee's energy
is getting low, so she's more susceptible to injury. Rest is important too." with her own line "Phew...
I guess I'm just... a little tired..." (`297b931b`). The client never prints an Energy number, so the bar
is the only readout, and the usable signals are its fill colour, which changes at the top of the range,
and that warning text. Between the two a Trainer can identify the low band but cannot measure it. The
tile's failure percentage is the one Energy-adjacent number the client does state, and whether it can be
inverted to an Energy value is with the research pass.

The training screen (`b1332cb3`): Speed facility **Lv4** with the banner "Floor Cleaning", three card
avatars on the tile, preview **+18 Speed / +8 Power / +6 Skill Points**, and **Failure 39%**. Facility
levels: Speed 4, Stamina 4, Power 4, **Guts 5**, Wit 4.

---

#### 1.8 Team Info panel

The control beside the Team Rank display, opened (`3cc1c271`, `556e1d6c`):

| Field                 | Value                                                       |  |
| --------------------- | ----------------------------------------------------------- |  |
| Team                  | Blue Bloom, motto "Dreaming Big", league **8th**            |  |
| Team Rank             | S (five stars), **Team Ranking Bonus: All Attributes + 30** |  |
| Team stat grades      | Speed A, Stamina A, Power A, **Guts S**, Wit A              |  |
| Unity Trainings       | **55**                                                      |  |
| Spirit Bursts         | **6**                                                       |  |
| Extreme Spirit Bursts | **5**                                                       |  |
| Members               | **20 / 20**                                                 |  |

Two of these are decision-bearing. **11 combined bursts** (6 + 5) sits in the 10–12 band, so the
November event pays the **gold** scenario skill at Lv1, and 13 would pay it at Lv3. And the team's
highest grade is **Guts (S)**, which is what selects the stat variant, so the incoming gold skill is
**Burning Spirit GUTS**. The five Extreme Bursts also explain the four Ignited Spirit hints on the Learn
screen, which is the mechanic at `02-unity-cup.md:109` rather than anything from the deck's hint pools.

The Team tab lays the roster out as five distance columns, Sprint, Mile, Medium, Long and Dirt, with an
**ACE** slot plus two more in each, matching the one-to-three-per-distance rule at
`02-unity-cup.md:170`. Reading the ACE row off the aptitude tags: Sprint ACE Seeking the Pearl (Sprint
A), Mile ACE Nishino Flower (Mile A), Medium ACE Tokai Teio (Medium A), Long ACE the trainee herself
(Long A), Dirt ACE Haru Urara (Dirt A).

#### 1.9 The roster

Every member carries a stat line in the client. The six deck characters appear as teammates in their own
card popups; the rest arrive as **Guest** R cards tagged `[Tracen Academy]`. Format is
Speed / Stamina / Power / Guts / Wit, then the caps the client shows.

| Deck character     | Grade | Stats                            | Caps                  | Aptitudes of note                        |  |
| ------------------ | ----- | -------------------------------- | --------------------- | ---------------------------------------- |  |
| Haru Urara         | **A** | 733 / 572 / 728 / **1068** / 621 | 900/860/900/1140/850  | Dirt A, Sprint A, Late A; Turf G         |  |
| Tokai Teio         | B     | 885 / 571 / 702 / 754 / 597      | 1080/860/960/880/850  | Turf A, Medium A, Pace A                 |  |
| Nishino Flower     | B     | 806 / 591 / 680 / 625 / 610      | 1080/860/960/880/850  | Turf A, Sprint A, Mile A, Pace A, Late A |  |
| Super Creek        | B     | 563 / 888 / 527 / 764 / 633      | 860/1100/860/1000/850 | Turf A, Medium A, Long A, Pace A         |  |
| Biko Pegasus       | C     | 776 / 534 / 660 / 593 / 534      | 1080/860/960/880/850  | Turf A, Sprint A, Late A                 |  |
| Matikanetannhauser | C     | 431 / 418 / 423 / 509 / 377      | 710/700/710/750/700   | Turf A, Medium A, Long A, Pace A, Late A |  |

| Guest teammate     | Grade | Stats                       | Caps                | Aptitudes of note                                |  |
| ------------------ | ----- | --------------------------- | ------------------- | ------------------------------------------------ |  |
| Fine Motion        | B     | 643 / 599 / 620 / 664 / 549 | 770/750/750/750/750 | Turf A, Mile A, Medium A, Pace A                 |  |
| King Halo          | B     | 603 / 556 / 633 / 725 / 526 | 800/750/770/750/700 | Turf A, Sprint A, Late A                         |  |
| Seeking the Pearl  | C     | 655 / 560 / 600 / 581 / 533 | 710/700/710/750/700 | Turf A, Sprint A, Mile A, Late A                 |  |
| Shinko Windy       | C     | 566 / 544 / 566 / 556 / 528 | 750/700/720/700/700 | Dirt A, Mile A, Pace A                           |  |
| Inari One          | C     | 576 / 597 / 616 / 556 / 569 | 700/720/750/700/700 | Turf A, Dirt A, Medium A, Long A, End A          |  |
| Nakayama Festa     | C     | 533 / 475 / 504 / 666 / 502 | 700/750/700/720/700 | Turf A, Medium A, Late A                         |  |
| Agnes Digital      | C     | 504 / 493 / 552 / 650 / 494 | 700/720/750/700/700 | Turf A, Dirt A, Mile A, Medium A, Pace A, Late A |  |
| Eishin Flash       | C     | 540 / 501 / 497 / 568 / 542 | 750/700/720/700/700 | Turf A, Medium A, Long A, Late A                 |  |
| Matikanefukukitaru | C     | 501 / 483 / 513 / 532 / 543 | 830/780/780/790/900 | Turf A, Medium A, Long A, Late A                 |  |
| Taiki Shuttle      | C     | 411 / 411 / 467 / 398 / 375 | 750/700/720/700/700 | Turf A, Sprint A, Mile A, Pace A                 |  |
| Gold Ship          | C     | 475 / 426 / 455 / 403 / 389 | 700/750/700/720/700 | Turf A, Medium A, Long A, End A                  |  |

That is 17 named members plus the trainee. The Roster grid's bottom row is clipped in the capture, so
two members are unaccounted for against the 20/20 count.

The guests carry their own skill lists, which is where the R-card hint pool comes from
(`02-unity-cup.md:95`): Seeking the Pearl (Firm Conditions ○, Pace Strategy, Sprinting Gear, Watchful
Eye, Sprint Straightaways ○, Unyielding Spirit); Taiki Shuttle (Preferred Position, Prepared to Pass,
Productive Plan, Updrafters, Mile Straightaways ○, Shifting Gears); Agnes Digital (Lay Low, Calm in a
Crowd, Mile Straightaways ○, Opening Gambit, Medium Straightaways ○, Late Surger Straightaways ○);
Shinko Windy (Wet Conditions ○, Competitive Spirit ○, Prepared to Pass, Hesitant Front Runners);
Matikanefukukitaru (Hakodate Racecourse ○, Trick (Rear), A Small Breather, Triple 7s, Calm in a Crowd,
Late Surger Corners ○, Lucky Seven); Inari One (Spring Runner ○, Target in Sight ○, Sharp Gaze, Forward
March!, Winter Runner ○, Ramp Up, Tail Held High, Familiar Ground); Eishin Flash (Standard Distance ○, <!-- lore-ignore-line class=3 cite=C-4 -->
Late Surger Straightaways ○, Fighter, Straightaway Acceleration, Late Surger Corners ○); Gold Ship
(Standing By, Inside Scoop, Smoke Screen, After-School Stroll, Intense Gaze, Highlander, Straightaway
Spurt, Pressure, I Can See Right Through You, Strategist, End Closer Savvy ○, Uma Stan); Nakayama Festa
(Wet Conditions ○, Slick Surge, Steadfast, Sharp Gaze, All I've Got, Long Shot ○, Outer Swell, Late
Surger Straightaways ○, Lucky Seven); Fine Motion (Right-Handed ○, Outer Post Proficiency ○, Nimble
Navigator, Straightaway Acceleration, Fall Runner ○); King Halo (Firm Conditions ○, Outer Post
Proficiency ○, Wait-and-See, Cloudy Days ○, Corner Recovery ○, Gap Closer).

Note the caps: teammates top out between 700 and 1140, not at the trainee's 1300/1800, so a team Guts
grade of S is being carried by Haru Urara's 1068 rather than spread evenly.

---

### 2. The skill pool, and what 173 SP buys

Fourteen Learn-screen scrolls give the whole purchasable pool with its client prices. This is the
section the first pass could not write.

#### 2.1 The hint discount ladder, read off the client

The Learn screen prints the discount itself, `Hint Lvl N` above `NN% OFF`:

| Caption        | Discount | Observed on                                                                                                                                                                |  |
| -------------- | -------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |  |
| `Hint Lvl 1`   | 10%      | Rushing Gale!, Outer Swell, Ignited Spirit SPD/PWR, Plan X, Kyoto Racecourse ○, Resplendent Red Ace, Firm Conditions ○, Sunny Days ○, Inner Post Proficiency ○, Gap Closer |  |
| `Hint Lvl 2`   | 20%      | Hesitant Pace Chasers, Cut and Drive!, Countermeasure, Hakodate Racecourse ○                                                                                               |  |
| `Hint Lvl 3`   | 30%      | It's On!, Ignited Spirit STA, Front Runner Corners ○, Productive Plan                                                                                                      |  |
| `Hint Lvl 4`   | 35%      | Unruffled, Pace Chaser Straightaways ◎, Pace Chaser Corners ○, Ignited Spirit GUTS                                                                                         |  |
| `Hint Lvl Max` | 40%      | Soft Step                                                                                                                                                                  |  |

Displayed cost is base × (1 − discount), floored to the integer. Bases 130, 160, 180 and 200 all
reconcile: 130 → 104/91/84, 160 → 144/128/112/96, 180 → 162, 200 → 180/140/130. The one fractional case
is Pace Chaser Corners ○, 130 × 0.65 = 84.5 shown as 84.

This is the in-client read the repo was waiting on. It is recorded in
`docs/research-scratch/SKILLS-MECHANICS.md` §2.4 and it closes the open gap in
`docs/UMAMUSUME_REFERENCE.md` §1.1.4. It also explains the levels this deck produces: Burst hints start
at card hint bonus + 2, +1 more for a scenario-linked card (`02-unity-cup.md:95`), and Nishino Flower
carries Hint Levels Lv2, which lands exactly on the Lv4 captions seen on the Pace Chaser line.

#### 2.2 Purchasable at or under 173 SP

| Skill                       | Cost | Hint   | Gate or note                                                   |  |
| --------------------------- | ---- | ------ | -------------------------------------------------------------- |  |
| Pace Chaser Straightaways ◎ | 91   | Lv4    | Style match. The tier above the ○ she owns                     |  |
| Pace Chaser Corners ○       | 84   | Lv4    | Style match                                                    |  |
| Straight Descent            | 120  | none   | (Pace Chaser)                                                  |  |
| Subdued Pace Chasers        | 130  | none   |                                                                |  |
| Subdued Front Runners       | 117  | Lv1    |                                                                |  |
| Hesitant Pace Chasers       | 104  | Lv2    |                                                                |  |
| Ignited Spirit GUTS         | 130  | Lv4    | Scenario skill, late-race vigor scaled to team Guts total      |  |
| Ignited Spirit STA          | 140  | Lv3    | Scenario skill, mid-race recovery scaled to team Stamina total |  |
| Cut and Drive!              | 160  | Lv2    | Front-half, last 200m                                          |  |
| Outer Swell                 | 162  | Lv1    | (Late Surger). She is Late B                                   |  |
| Highlander                  | 160  | none   |                                                                |  |
| Ramp Up                     | 170  | none   |                                                                |  |
| Straightaway Acceleration   | 170  | none   |                                                                |  |
| Calm in a Crowd             | 170  | none   |                                                                |  |
| Soft Step                   | 96   | Lv Max |                                                                |  |
| Countermeasure              | 128  | Lv2    | (Sprint). She is Sprint E                                      |  |
| Gap Closer                  | 144  | Lv1    | (Sprint)                                                       |  |
| Productive Plan             | 112  | Lv3    | (Mile)                                                         |  |
| Long Shot ○                 | 81   | Lv1    | 4th favourite or below                                         |  |
| Kyoto Racecourse ○          | 81   | Lv1    | This turn's Daishoten                                          |  |
| Hakodate Racecourse ○       | 56   | Lv2    |                                                                |  |
| Firm Conditions ○           | 81   | Lv1    |                                                                |  |
| Sunny Days ○                | 81   | Lv1    |                                                                |  |
| Inner Post Proficiency ○    | 81   | Lv1    |                                                                |  |
| Front Runner Corners ○      | 91   | Lv3    | Wrong style                                                    |  |

The Unity Cup line on the Learn screen is **Ignited Spirit SPD / STA / PWR / GUTS** at 180/140/180/130,
all four on a base of 200 and separated only by hint level. The series runs to five per the client
strings (速 / スタ / パワ / 根性 / 賢さ), so an Ignited Spirit WIT exists and simply is not hinted in
this deck; the frames not showing it is not a gap in the series
([game8.jp, ⚠️ STALE: 2025-11-20](https://game8.jp/umamusume/400803), names cross-checked against
[game8.co, 2026-09-23](https://game8.co/games/Umamusume-Pretty-Derby/archives/563893)). Four of the
five are hinted here, and the Team Info panel says why: **6 Spirit Bursts plus 5 Extreme Bursts**, the
Extremes being what grants the matching facility's Ignited Spirit hint (`02-unity-cup.md:109`). No
**Burning Spirit** entry appears because the gold half of the line (燃焼) is paid by the November event
rather than by a Burst, and that event has not fired yet. At 11 combined the event pays gold Lv1, and
two more bursts take it to Lv3. See §2.4.

**Out of reach at 173:** Ignited Spirit SPD and PWR 180, Resplendent Red Ace 180, Plan X 272, Unruffled
280, It's On! 289, Rushing Gale! 323.

#### 2.3 Recommended spend

Advice, not a prediction.

1. **Pace Chaser Straightaways ◎, 91.** Cheapest style-matched velocity on the board, and the tier
   above a skill she already holds.
2. **One 81 with the remaining 82, chosen for the race you actually run.** Kyoto Racecourse ○ fires at
   the Daishoten this turn. The going there is **Soft**, so Firm Conditions ○ would not fire, while the
   sun on the race card makes Sunny Days ○ the condition skill that would. Long Shot ○ is now the
   weakest of the four: the client's own Predictions verdict for this race is "top contender", and the
   skill needs 4th favourite or below.
3. **Scenario skills: hold off until the November question is answered.** The Team Info count says the
   event will pay **Burning Spirit GUTS** (gold) at Lv1, and the team's Guts grade is what selects it.
   Buying the white **Ignited Spirit GUTS** now, at 130, is either the prerequisite for that gold or a
   skill the gold supersedes, and neither the corpus nor the frames state which; the catalog's skill
   table carries no prerequisite column either. That single unknown decides whether 130 SP is an
   investment or a write-off, so it is the one thing worth checking in the client before spending. If
   the gold is coming regardless, **Ignited Spirit STA at 140** is the better buy of the two on this
   build: 577 Stamina over 2500 m is her actual weakness, and the Guts side is already carried.
4. **Do not buy into a gate she fails.** Countermeasure and Gap Closer are Sprint, Productive Plan is
   Mile, Outer Swell is Late Surger, Front Runner Corners is the wrong style.
5. **Save It's On! and Unruffled for a longer run.** Both are golds this deck can hint, and neither is
   reachable now.

#### 2.4 The burst economy, and the one deadline inside the five turns

From the research pass, with what the frames add:

- **Charge.** A teammate's gauge moves one quarter per Special Training she joins, so roughly four
  sessions fill her
  ([umamusu.wiki, 2026-09-17](https://umamusu.wiki/Game:Unity_Cup);
  [famitsu, ⚠️ STALE: 2021-09-02](https://www.famitsu.com/news/202109/02232227.html) gives the same
  "about four"). No source publishes a per-turn curve beyond that.
- **One normal Burst and one Extreme per teammate per career.** The Extreme is a low-probability
  follow-on, it can be skipped and come back, and it locks after one activation
  ([kamigame, ⚠️ STALE: 2024-04-10](https://kamigame.jp/umamusume/page/172795448925364187.html);
  [game8.co 545572, 2026-07-07](https://game8.co/games/Umamusume-Pretty-Derby/archives/545572)). Both
  kinds count toward the same career total (`02-unity-cup.md:201`, confirmed by
  [gametora, 2026-07-20](https://gametora.com/umamusume/unity-cup)).
- **The count is cashed at one scripted event, not continuously.** "Team Zenith Declares War" pays the
  scenario hint by total bursts: 4–6 white Lv1, 7–9 white Lv3, 10–12 gold Lv1, 13+ gold Lv3, with the
  stat variant chosen by the team's highest overall rank and the value scaling on the team's total in
  that stat. **This run reads 11 combined (6 + 5) on the Team Info panel, inside the 10–12 band, so the
  gold is already banked at Lv1 and the only question is whether two more bursts land in time for Lv3.**
  The team's highest grade is Guts (S), so the variant is GUTS.
- **Deadline.** That event is in Senior November, which is inside the five turns. Bursts fired after it
  do not raise the tier, so charging and firing belongs early in the window, not before the finals.
- **Readout.** The running count is available on demand: the control beside the Team Rank display opens
  combined drill counts and burst activations
  ([game8.co 545572, 2026-07-07](https://game8.co/games/Umamusume-Pretty-Derby/archives/545572),
  matching `02-unity-cup.md:212`). That panel is captured, in §1.8.

**Three discrepancies against `docs/scenarios/02-unity-cup.md`, flagged rather than fixed:**

1. **Date.** That file's `:210` says the payout event is Senior **late** November and records that it
   had deliberately moved the date off "early November". The publishers read the other way: game8.jp
   prints シニア11月前半の練習後, "after practice in the **first half** of November"
   ([⚠️ STALE: 2025-11-20](https://game8.jp/umamusume/400803)), and game8.co says "Senior Year Early
   November" ([2025-11-07](https://game8.co/games/Umamusume-Pretty-Derby/archives/563908)). gamewith
   words it as the start of November's second half
   ([⚠️ STALE: 2023-01-20](https://gamewith.jp/uma-musume/article/show/287164)), which is the only
   support the late reading has. Planning consequence either way is the same, so this is a doc-currency
   question, not a strategy one.
2. **Naming.** `:210` withdraws the "Ignited Spirit X" / "Burning Spirit X" tier names in favour of a
   white/gold framing. The client prints those names: four Learn entries read **Ignited Spirit SPD / STA
   / PWR / GUTS** (`c1ee4d11`, `c8b2dcad`), and the publishers carry the gold counterpart as Burning
   Spirit ([game8.co, 2026-09-23](https://game8.co/games/Umamusume-Pretty-Derby/archives/563897)). The
   white/gold framing is a fine description; the claim that the names were superseded is contradicted by
   the screen.
3. **Payload.** The `:205-208` table's "+10 stat / +10 SP … +40 / +40" is gametora's
   ([2026-07-20](https://gametora.com/umamusume/unity-cup)) and is single-source. game8.co
   ([2026-07-07](https://game8.co/games/Umamusume-Pretty-Derby/archives/545572)) and game8.jp
   ([2025-11-20](https://game8.jp/umamusume/400803)) print **15 / 15 / 20 / 20 SP** with no stat
   component at all. Two publishers against one, on a figure this file treats as settled.

---

### 3. What this run looks like it was doing

The build is coherent and unusual: Guts as the primary stat (736, highest on the board) with +20%
growth, two Guts cards, Super Creek carrying Stamina, and three Speed cards doing base work. Wit is
deliberately abandoned at 397, and the deck has no Wit card to fix it. Medium and Long at A with Pace
A, plus Swinging Maestro already owned, so Arima Kinen at 2500 m is a race she is shaped for. All four
team rounds won and Team Rank S banked means both headline Unity Cup objectives are already secured.

---

### 4. What is closed off, and one thing the corpus cannot settle

The Elite Team path is **past its window**, not necessarily failed. The condition is checked during the
**4th Team Race** (`02-unity-cup.md:159`), which this run already won, after Senior Late Jun. Whether
an Elite team actually appeared there is not in any frame.

The first pass of this file called the path unavailable because placement is 8th and the corpus states
the condition as "league rank is ≥ 10" (`02-unity-cup.md:161`). That reading is not safe: the doc
writes the rank as a number with a floor sign, and a league position of 8th satisfies "top 10" while
failing "10 or higher". The two readings invert the verdict. Flagged for the owner rather than fixed
here, since it is a doc defect in `docs/scenarios/02-unity-cup.md`, not a finding about this run.

Against the three Elite conditions (`02-unity-cup.md:161-163`): Team Rank S clears the ≥ A bar, and at
least one Extreme Burst has almost certainly fired, since four distinct Ignited Spirit hints are on the
Learn screen and the Extreme Burst is what pays them. That leaves the league-rank reading as the only
thing standing between this run and the path, and the window closed anyway at Round 4.

What follows either way: the finals opponent is standard Team Zenith unless the strengthened variant was
unlocked, and beating Zenith while at Rank S is what grants the team name's skill, **Cooldown** for Blue
Bloom (`02-unity-cup.md:183`, `:156`). Picking the name is not enough; the win is.

---

### 5. The plan for the five remaining turns

1. **Energy first, this turn.** A 39% failure roll on a strong tile is a bad trade with five turns
   left. In order: an Extreme Burst tile (0% failure, scoped to the facility holding the Burst,
   `02-unity-cup.md:113`), a Wit turn (no Energy cost, and a Burst there returns +5 Energy), or Rest
   for Energy. Corrected 2026-10-03: this step was written as "Rest for +30 Energy" on
   `docs/UMAMUSUME_REFERENCE.md:162`, and the repository's own §2.1 in
   `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` measures the modal rest at +50 with +30 as
   the under-slept failure tier. The order of the three options does not depend on which tier lands;
   how many training turns the rest buys back does, so the figure is left open here rather than picked.
2. **Run the Kyoto Daishoten rather than training into the risk.** It is scheduled Early Oct, it costs
   no Energy, and Race Bonus effects on the deck pay stats and fans from finishing: 6,700 fans on a win,
   which is 22% of the gap to Top Star. The G1 on the same card is Morioka Dirt 1600m and she is Dirt G,
   so the higher grade is the worse ride.
3. **Then committed training turns on the best tiles.** Guts is Lv5 and holds both Guts cards, so it is
   the highest-value facility, and the multi-flame curve rewards crowding: the trainee gains nothing
   below 2 flames, and 5 flames is 10 primary / 5 secondary / 7 SP
   (`02-unity-cup.md:77`, `:83-90`).
4. **Two more bursts before the November cut-off, to turn the gold Lv1 into a Lv3.** The count is
   already 11, so Burning Spirit GUTS is banked at Lv1; 13 lifts the level. Early Nov is the third of the
   five turns and Late Nov the fifth, so the window is two or three turns wide depending on how the date
   discrepancy in §2.4 resolves. On the estimate that a gauge needs four sessions, 11 bursts accounts for
   44 of the 55 Unity Trainings logged, leaving about eleven sessions of partial progress spread across
   the members, so two fills inside the window is arithmetic that works if the tiles are chosen for it.
   A Burst fired after the event still raises stats but no longer buys a tier.
5. **Enter Late Dec at full Energy and GREAT mood.** Late Dec holds both Arima Kinen and the Unity Cup
   finals. Mood is already GREAT, so Recreation is only worth it if an event drops it.
6. **Spend the 173 SP per §2.3.** The first pass could not say what was affordable; it now is.

---

### 6. What is still missing

Three sources closed items during the session: the 17:07 set, the seeded catalog, and the 17:53 race-day
and Sparks panels. The Learn screen, the trainee's Skills tab and the Perks aggregate were already in
hand from 16:27.

**Closed this round:**

- **Burst numbers.** Team Info reads 55 Unity Trainings, 6 Spirit Bursts, 5 Extreme Spirit Bursts
  (§1.8). That is the input §2.4 and §5 needed.
- **The per-card skill lists.** Read from the catalog's `hint_skills` and `event_skills` (§1.4) rather
  than from a tab that was never opened, and they reconcile with every hint level on the Learn screen.
- **Team roster and Team Race composition.** §1.9, minus two members clipped out of the capture.
- **Team Power, as far as the client shows it.** Grades A/A/A/S/A plus the +30 ranking bonus (§1.8).
  No single power total appears on that screen.
- **Deck effect sets.** The catalog carries each card's full effect list at maximum values (§1.4),
  including the rows the client has locked behind card level.

- **Ancestry tree.** Six Sparks panels, one per ancestor, plus the Legacy panel (§1.5). The tree is
  Vodka under Daiwa Scarlet and Seiun Sky, and a Guest Maruzensky under Daiwa Scarlet and Taiki Shuttle.
- **This turn's race card, and a proxy for the favourite question.** The Daishoten's full spec is in
  §1.6, and the client's own "top contender" verdict stands in for the starting grid.

**Still open.** Items 1, 2, 3 and the league-rank half of 4 went to an online research pass on
2026-10-03, since the Trainer has no in-client answer for them either.

1. **The white-to-gold question.** Whether owning Ignited Spirit GUTS is a prerequisite for the gold
   Burning Spirit GUTS the November event will hint, or is superseded by it. It decides a 130 SP buy.
   Nothing in the repo, the catalog or the frames answers it, and the client has no screen that states a
   skill prerequisite.
2. **What the Unity Cup finals actually are as a race.** Whether the finals match has a venue, distance
   and ground at all, what sets the opposing lineup, and what the win grants beyond the team-name skill.
   The pool offers Kyoto ○ and Hakodate ○ and **no Nakayama**, so nothing maps the Arima Kinen to a
   venue skill either.
3. **Cap composition, remainder.** Now a measured shortfall rather than an unexamined number: the six
   blue sparks in §1.5 account for the Guts +8 and nothing else (§1.2). What raises the other three
   extras, and whether any Global card carries a non-zero Max-Stat value, is unresolved.
4. **League placement and NPC bonds.** How the placement number is computed, and whether 8th satisfies
   or fails the Elite Team's "league rank is ≥ 10" (§4). Bond state has no screen the Trainer can point
   to, so it is likely not obtainable from frames at all.
5. **Exact Energy value.** The client prints no number; the readable signals are the bar colour and the
   warning text (§1.7). Whether the tile's failure percentage can be inverted to a value is with the
   research pass.
6. **Race history.** The Log reads "There is no log" in every frame and cannot be supplied. Closed as
   unavailable; the Predictions verdict is the substitute for the favourite question, and the Long Shot
   ○ call in §2.3 is made on it.
7. **Two roster entries.** The Trainer confirms §1.9 lists every Guest teammate, which makes the named
   total 18 against the panel's 20/20 counter. Either the clipped row repeats characters already listed
   or the count includes someone the grid does not show. Left as an arithmetic discrepancy, not a
   screenshot gap.

---

### 7. Corpus findings, and the edits they caused

Eight findings came out of the 67 frames, the research pass and the catalog cross-check: two are
recorded in the corpus, one is corroboration, two are flagged rather than applied, and three are things
the later sources revealed that the frames alone had wrong or left unstated.

1. **The per-level hint discount** is now client-read: 10/20/30/35/40% at Lv1/Lv2/Lv3/Lv4/`Lv Max`.
   Recorded in `docs/research-scratch/SKILLS-MECHANICS.md` §2.4 with its caption table and arithmetic,
   and it closes the `❌ UNVERIFIED` line in `docs/UMAMUSUME_REFERENCE.md` §1.1.4 plus the matching
   entry in §8.4's remaining-gaps list. §2.2's standing position that a percentage may not be rendered
   in application code, and conflict row 16, were left alone; those are the owner's to revisit.
2. **The export's `stat_bonus` row is the Growth Rate display.** Her Details screen carries Potential
   Level as a single number and, separately, a five-cell Growth Rate row reading +0/+10/+0/+20/+0,
   which is `stat_bonus: [0, 10, 0, 20, 0]` in GameTora's column order, position for position. The
   three July captures of this same trainee agree, and Special Week's published pair agrees across two
   sources. The costume-varying evidence (`[0,10,0,20,0]`, `[0,15,15,0,0]`, `[0,20,0,10,0]` across her
   three) is what retires the old Potential Level guess, which the first pass of this file had left
   standing as "very likely". Recorded in `docs/UMAMUSUME_REFERENCE.md` §1.3.5 and the §3.1 reading
   note. Scope stated there: two trainees, one of them read in-client by this document.
3. **The Mood Effect panel re-confirms §1.1.6** at ±20% training and ±4% running. No edit; the reference
   already carries these figures and the client agrees with them.
4. **`docs/scenarios/02-unity-cup.md` needs three corrections and two confirmations** (§2.4). The
   confirmations: the combined normal-plus-Extreme count (`:201`) and the Team Info readout beside the
   Team Rank (`:212`) both hold up against publishers. The corrections: the payout event is dated late
   November at `:210` where two publishers print the first half of the month; the payload row at
   `:205-208` rests on one publisher against two that report 15/15/20/20 SP and no stat component; and
   `:210`'s withdrawal of the "Ignited Spirit" name is contradicted by the client, which spells it on
   four Learn entries. The date and the payload are publisher conflicts this repo has already ruled on,
   so they are yours to reopen. The name is not a judgment call, and that line is wrong as written.

5. **The client prints a Global team-rank bonus, and `02-unity-cup.md` does not carry it.** The Team Info
   panel's tooltip reads "Team Ranking Bonus / All Attributes + 30" beside the S emblem (`3cc1c271`).
   That file's `:152` explicitly sets the "+50 all stats at Rank 5+" figure aside as `[JP]`-side and says
   to treat the letter ladder as the live Global one, but it never states what the Global ladder pays.
   This is the first Global number for it, and it is flagged rather than written in because the tooltip's
   binding to the rank, rather than to the 8th-place league standing it sits under, is inferred from
   position on screen.
6. **The catalog beat the frames on the deck, and corrected this file twice.** The epithet is
   `[Just Keep Going]` with no trailing exclamation mark, in the catalog and in the client header; the
   first pass printed one. And the number beside a padlock in a card popup is the card level the effect
   unlocks at, not a percentage; the first pass listed those as values. Both are in §1.4 now. The
   scenario-link claim this file previously supported with a corpus citation is available better: the
   application derives it, and `SupportCard::isScenarioLink('unity_cup')` returns yes for Haru Urara and
   no for the other five, against `config('scenarios.php')`.

7. **Star ranks have to be read off the per-ancestor Sparks panel, not the scrolling grid.** The grid
   gave Shooting for Victory! ★★★, Resplendent Red Ace ★★☆ and a Guts ★★★ that does not exist in the tree;
   the panels give ★★☆, ★☆☆ and nothing. That is not cosmetic: the cap arithmetic in §1.2 was built on
   the grid values, and against the real six blue sparks three of the four cap extras stop being
   reproducible. Worth carrying into the corpus's inheritance section as a method note.
8. **Two facts the card popups cannot show.** The sixth deck slot is a **friend card**, visible only as a
   "Friends" pill on the Career Profile rail, which is why the deck's only 50/50 card is not the
   trainer's own. And the going on this turn's race is **Soft**, which invalidates the Firm Conditions
   recommendation the first version of §2.3 made while conditions were still unknown.

Verification run on those edits: retired literals grep to zero, each new marker greps to one, the
concurrent thread's two uncommitted hunks in `docs/UMAMUSUME_REFERENCE.md` are still present in the
diff, added lines are clean against the banned-vocabulary pattern, and `composer lore` reports its
pre-existing 187/76 baseline. Nothing is committed.

---

### 8. Research pass results, 2026-10-03

The four open questions went to an online pass over Global and JP publishers. This section records what
came back and names each place above it supersedes. Publisher dates are given; the repo marks anything
older than 90 days, and this scenario was reworked in July 2026, so pre-rework pages are flagged rather
than trusted.

#### 8.1 The white-to-gold question is answered: the white is the prerequisite

`§6 item 1` and `§2.3 step 3` are closed. game8.co's Burning Spirit page lists the Ignited Spirit
variant as its prerequisite and calls it the superior replacement
([2026-09-23](https://game8.co/games/Umamusume-Pretty-Derby/archives/563898)), and the JP side mirrors it
both ways: game8.jp's アオハル点火・速 carries 「上位スキル：アオハル燃焼・速」
([2026-10-02](https://game8.jp/umamusume/511396)) and GameWith's アオハル燃焼・速 lists the 点火 skill as
its 下位スキル ([2026-09-28](https://gamewith.jp/uma-musume/article/show/292878)). So spending 130 SP on
**Ignited Spirit GUTS is an investment, not a write-off.** That reverses §2.3's hold recommendation.

Still not established, and it matters: whether learning the gold suppresses or replaces the white in a
race. No page on either side states it. The tiering plus a shared mid-race trigger and a shared scaling
input is the shape of a strict upgrade, but that is inference and is recorded as such.

The base price closes a loop with §2.1: publishers give **200 SP** for both tiers, and this run's client
shows Ignited Spirit GUTS at 130, which is 200 × 0.65, the Lv4 discount. An independent source confirms
the base the discount arithmetic was derived from.

#### 8.2 Whether Extreme Bursts count toward the gold is a live disagreement, and it decides this run

`§2.4` and `§1.8` assert 11 combined bursts puts the run in the gold band. That holds only if Extremes
count, and the sources split:

- gametora: tiers are based on "the number of Spirit Bursts **and Extreme spirit bursts** done during the
  career" ([2026-07-20](https://gametora.com/umamusume/unity-cup)). Combined 11 → **gold Lv1**.
- umamusu.wiki: the Burning Spirit criteria read "At least 10 Spirit Bursts by Senior Year Late November"
  and never say whether Extremes increment it
  ([2026-09-17](https://umamusu.wiki/Game:Unity_Cup)).
- game8.jp: 「アオハル爆発を行った回数」, no mention of 極 in the counting rule
  ([⚠️ STALE: 2025-11-20](https://game8.jp/umamusume/400803)).

If only normal bursts count, this run is at 6 and the November event pays **white Lv3 plus 15 SP**, not
gold. The plan in §5 step 4 changes shape under that reading: two more bursts would be reaching the 7–9
white band's ceiling rather than the 13 gold Lv3 step. Do not treat either outcome as settled; the count
is on the Team Info panel and the client is the tiebreaker.

The same page adds a third acquisition route worth noting: umamusu.wiki lists the Ignited variant as
obtainable from an Extreme Burst during matching training **and by inheritance**, which matches the
★☆☆ "Ignited Spirit: Guts +" spark from Maruzensky in §1.5.

#### 8.3 The finals are five races, not one, and that is why no single race card exists

`§6 item 2` is closed. umamusu.wiki's row reads "The Unity Cup Finals | Senior Year Late December | 5 |
Team Zenith" and the set "includes all distances on turf and a mile race on dirt"
([2026-09-17](https://umamusu.wiki/Game:Unity_Cup)); the JP original names the five as 短距離／芝,
マイル／芝, 中距離／芝, 長距離／芝, マイル／ダート, with venues fixed by the opponent side and the
finals opponent locked to Riko Kashimoto's team
([⚠️ STALE: 2024-08-30](https://umamusume.wikiru.jp/)), which the official JP page confirms as
「VSチーム＜ファースト＞」「全部で5戦！」 ([undated, official](https://umamusume.jp/contents/game/scenario/aoharu/)).

This is what the client's Team tab five-column layout was for: the Sprint, Mile, Medium, Long and Dirt
columns in §1.8 **are** the finals programme, so the ACE per distance is the lineup that fights it. The
question "what are the finals' distance, venue and ground" was the wrong question, and §2.3's condition
skill should be chosen for the Arima Kinen and for the five-category meet, not for one race.

Rewards beyond the team-name skill are corroborated: a finals win grants the team-name gold, improves
scenario-spark odds, and unlocks the "+" scenario spark variants.

#### 8.4 The league-rank ambiguity resolves in favour of this run: 8th qualifies

`§4` and `§7 item 4` are settled. The JP condition is 「チームランキングが10位以上」
([⚠️ STALE: 2024-08-30](https://umamusume.wikiru.jp/);
[⚠️ STALE: 2023-01-20](https://gamewith.jp/uma-musume/article/show/287164)), and 10位以上 means 10th or
better. The Global renderings agree once translated: umamusu.wiki says "demands **placing tenth or
above**" ([2026-09-17](https://umamusu.wiki/Game:Unity_Cup)) and gametora "if your league rank is at
least 10" ([2026-07-20](https://gametora.com/umamusume/unity-cup)). So `02-unity-cup.md:161`'s
"league rank is ≥ 10" is a ranking-quality condition, **8th satisfies it**, and the run's own Elite Team
gate reads: rank S clears the ≥ A bar, at least one Extreme fired, league 8th qualifies. All three held
going into Round 4. Whether an Elite team actually appeared there is still not in any frame.

Also from the JP pages: the league starts at 30th and moves by beating higher-ranked teams, which is
what "8th" is measured against.

#### 8.5 The cap shortfall gains a named candidate, and one mechanism is ruled out

`§1.2` and `§6 item 3` narrow. Two changes:

- **Bursts are ruled out for the trainee's own caps.** The official JP page says 「チームメンバーの能力が
  上限を超えて大幅アップ！」 ([undated, official](https://umamusume.jp/contents/game/scenario/aoharu/))
  and both Global sources agree the target is the teammate
  ([umamusu.wiki 2026-09-17](https://umamusu.wiki/Game:Unity_Cup),
  [gametora 2026-07-20](https://gametora.com/umamusume/unity-cup)). So the 5 Extremes this run fired do
  not explain her 1325/1322/1368/1308.
- **Green (unique-skill) factors are the candidate.** GameWith states cap-UP lives in blue and green
  factors, and that a green factor raises the cap of the stat tied to that character
  ([⚠️ STALE: 2023-06-28](https://gamewith.jp/uma-musume/article/show/360428)); game8.jp puts its value at
  the character's growth rate ([⚠️ STALE: 2025-11-21](https://game8.jp/umamusume/475668)). This run's tree
  carries **six** green factors: Cut and Drive!, Angling and Scheming, Resplendent Red Ace ×2, Red
  Shift/LP1211-M and Shooting for Victory!. No publisher gives the number, so the gap stays open, now
  with a mechanism attached to it.

The +4 / +9 / +16 table is published only for the career-start inheritance; the Classic and Senior April
magnitudes are unpublished, which is what §1.2's arithmetic has been assuming.

#### 8.6 Energy cannot be back-solved, and the client has since added a number

`§1.7` and `§6 item 5` are answered. Energy starts at 100 and its maximum is the same
([wikiru, 2026-09-06](https://umamusume.wikiru.jp/)); failure rate "starts to increase when energy is
below roughly half", ceiling 99%, floor 1% even at zero, and higher training level costs more, which is
what pushes an Lv4 tile's risk up. **No source makes Mood an input to the printed failure rate**, and no
publisher documents a band, a colour threshold or the warning string. So 39% is consistent with energy
under 50 and "about a third full" is consistent with that, but the value is not derivable. Record the
percentage as the client prints it.

One thing to go back and look for: the official Global account announced that "the numerical value for
your Umamusume's amount of energy will be displayed under the energy bar"
([~2025-10-24, official](https://x.com/uma_musu_en/status/1983143988082454858)). These frames show no
number under the bar, so either the build predates that change or the readout is elsewhere on the
screen. Worth one capture of the header region.

#### 8.7 One calendar inconsistency the pass could not resolve

Sources put the finals at Senior Late December, which is seven half-month turns after Senior Early
October, but the client prints "5 turn(s) until the Unity Cup" on this screen. Either two turns are
consumed by the November event and something else before the finals, or the turn counter uses a rule
these pages do not describe. Unresolved, and it is the client's own number, so the client wins for
planning purposes.

#### 8.8 Net effect on the plan in §5

Step 4 changes: buy the white Ignited Spirit GUTS at 130, because it is the published prerequisite for
the gold the November event may pay. Step 3's target changes shape depending on §8.2, and the burst
count on the Team Info panel is the tiebreaker rather than an assumption either way. Everything else in
§5 stands.
