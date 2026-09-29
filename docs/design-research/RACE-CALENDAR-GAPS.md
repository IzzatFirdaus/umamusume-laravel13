# Race calendar — gaps and open questions

**Date:** 2026-09-29
**Scope:** what the race read path does **not** know, what it knows twice, and what it was
asked not to decide.
**Not a to-do list.** Several entries are deliberately unresolved because resolving them is
either an owner decision or another slice's call site. "Owner" below names who can close it,
not who will.

Companion documents: `docs/scenarios/09-global-race-calendar.md` (the data and its evidence),
`docs/design-research/HANDOFF-RACE-READ-PATH-2026-09-29.md` (why the read path moved).

---

## 1. Two tables both hold goal races

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

## 2. Tier labels disagree between the seeder and the parser

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

## 3. The maiden rule is global in the source and per-row in the schema

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

## 4. `careerYearForTurn()` is an inference, and the grid depends on it

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

## 5. Track names and first-place fan figures are not resolvable

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

## 6. `trainee_goals`, and the picker that no longer waits on it

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

```
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

## 7. The Goal pennant has no source

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

## 8. `SourceFetcher` writes `.html` for JSON snapshots

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

## 9. A check that cannot fail for the reason it claims

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

## 10. Two identical free races sit in the development database

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
