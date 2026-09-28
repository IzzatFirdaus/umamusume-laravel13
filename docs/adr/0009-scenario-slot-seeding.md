# ADR-0009: Seeding `scenario_slots` — three sources, what each unlocks, what stays dark

Status: **PROPOSED, document only.** No migration, no seeder, no parser and no fixture is added by
this ADR, and it takes no decision: the choice belongs to the owner, per `AGENTS.md` escalation 2 and
the standing rule that an agent never adopts a scope change silently. It exists because the reason
`ADR-0003` Amendment R3 gave for deleting the seeder has been overtaken by events, and because the
brief that asked for this document (Slice 10 T4, R51-era) named three candidate sources and asked
what each one actually unlocks.

Date: 2026-09-29
Deciders: Architect (schema reading), Planner Domain Specialist (domain consequences), implementing
agent (draft). Owner decides.
Relates to: `ADR-0003` Amendment R3 (`docs/adr/0003-consolidated-phase1-schema-expansion.md:206-220`),
`ADR-0004` (reference data with provenance), `docs/scenarios/09-global-race-calendar.md`,
`research-scratch/data/json/{ura-races,race_instances,races}.json`, `D-227`, `D-228`, `D-20`,
`KI-10`, `KI-11`, `KI-15`, `KI-17`, `database/migrations/2026_09_27_153416_create_scenario_slots_table.php`

## Problem

`scenario_slots` is empty in every real database. A clean seed creates the table and puts no rows in
it; the stub seeder that would have held them was deleted (`5c65597`, `KI-11`'s first half) because
R3 ruled slot rows fetch-engine work with no sourced data to fetch.

Everything downstream that reads slots is therefore rendering an absence:

- `StoreRaceEntryRequest` requires a `scenario_slot_id` that exists and belongs to the run's scenario,
  so with no rows there is nothing a Trainer can enter a race against. Unity Cup and URA calendars
  are dark for the same reason Trackblazer free races are: not a missing form, a missing row.
- `TrainingRun::epithetProgress()` matches prerequisites against `completedRaceTitles()` by title
  (`app/Models/TrainingRun.php:786-805`), so an epithet route naming a race no one can record can
  only ever read as unmet.
- `KI-10`'s ratio half needs to know which race a placed finish came from; an entered finish with no
  slot behind it names nothing to attribute points to.
- `KI-17` is the same shape from the other side: `race_entries` points at a slot and never at a turn,
  so even a fully populated calendar would not make a consecutive-race count derivable.

R3's stated blocker was the source, not the schema: "no Oka Sho, no fan threshold, no month-and-half
placement for any URA target" (`ADR-0003:214-215`). A calendar with those three things now exists in
this repository, and a client export that predates it is on disk. So the question moved: not *whether
a source exists*, but *which source, through which mechanism, and into which schema*.

## The blocker that is not about sources

Whatever the answer, the current table cannot hold a real career calendar.

`scenario_slots` has `unique (scenario_key, month, half, kind)`
(`...create_scenario_slots_table.php:58-59`). The client and the calendar both put **several races in
the same month and half**: `docs/scenarios/09-global-race-calendar.md:115-117` lists three slots for
Early August of Junior Year (Cosmos Sho, Dahlia Sho, Phoenix Sho), all `goal_race`. One of the three
would insert; the other two would violate the key.

So seeding is a schema decision before it is a data decision, and the schema is the Architect's, cited
to a PRD requirement. Three shapes were considered, none adopted here:

1. Add a discriminator to the key (`race_id`/`source_id`, or the export's `instance`). Preserves the
   invariant the key was written for and makes the calendar's own shape expressible.
2. Move the calendar's grain: one slot per (scenario, month, half, kind) with a child table for the
   races in it. Larger change, cleaner model, and it collides with Slice 8's standing instruction not
   to add tables.
3. Keep the key and seed one race per slot, which is what the fixtures do today by deriving month and
   half from order. This is **not a faithful calendar**; it is a sample that fits the constraint, and
   it should not be mistaken for option 1 or 2 working.

A second schema question is already open on the page R3 pointed at: `tier` is a nullable free-text
column whose comment says `// G1, G2, OP, etc.` (`:46`), there is no tier enum in the codebase, and
`[Global]`'s client prints one label where the export's `gradeName` for code 400 reads `OP/L
(Open/Listed)` (`docs/scenarios/09-global-race-calendar.md:666`). A seeder that picks a tier set
picks a contract, so the tier question is decided *before* any import, not inside one.

And `is_mandatory` cannot mean what the column suggests: the calendar's own finding is that only the
Junior Make Debut and the scenario final are mandatory for every trainee, while Goal races are
per-character and four captured client panels show four different Goal sets
(`docs/scenarios/09-global-race-calendar.md:15-16`). Seeding `is_mandatory = false` on a Goal race is
accurate; seeding the Goal banner itself would need a per-character table that no source names.

## Option A — the client export (`research-scratch/data/json/`)

**What it is.** Three joinable tables already in the corpus: `ura-races.json` (413 rows: `instance`,
`month`, `half`, `year`, `fans_needed`, `fans_gain`, `tid`, `drops`), `race_instances.json` (413 rows:
`month`, `half`, `year`, `special_race`, plus English `description[]` and `details[]`), and `races.json`
(322 rows: `name_en`, `grade`, `terrain`, `distance`, `course`, `list_ura`, and a
`unreleased_servers` flag). Fan curves also exist server-qualified as `en__race-fans.json`.

**Readiness.** Highest field coverage of the three. `title` comes from `name_en`, `month`/`half` from
the instance row, `fans_needed` from `ura-races`, `tier` from `grade`, `description` from
`race_instances.description[]`, which is client text rather than a paraphrase. It is the only source
that can also say which races do **not** reach `[Global]` (`unreleased_servers: ['en']`), which is how
a seeded calendar can exclude rows on evidence rather than on a reader's memory.

**What it unlocks.** The race writer for URA and Unity Cup careers (rows exist to enter against), the
epithet checklist for routes whose races are career slots, and `KI-10` attribution for a placed finish
recorded against a named race.

**What stays dark.** Trackblazer `grade_deadline` and Unity Cup `team_race` slots: the export files in
hand are career-race tables, not a deadline or opponent table, so the Trackblazer panels would still
render an absence. The `scenario_key` is an inference from a filename (`ura-races.json` → `ura_finale`)
and the `list_ura` marker, not a column, so scenario attribution is this tool's judgement printed as
data. `year` is a 1-3 integer whose mapping to Junior/Classic/Senior is ours. And the sentinel months
are real: seven rows carry `month = 99999`, including the debut's flexing distance, so an importer that
coerced them to a calendar month would be inventing a date.

**The disqualifier today.** `research-scratch/` is not tracked (`git ls-files research-scratch` returns
nothing). A seeder cannot read a file that exists only on one machine, and the Data Engineer's rule is
that a fact without provenance is deleted, not stored: `source_url`, `snapshot_path`, `fetched_at` and
`source_timezone` would all describe a path no one else has. Making option A honest means either
committing the snapshot or running it through the fetch engine (`app/Services/DataPipeline/`:
one config entry in `config/uma.php`, one parser class, `Http::fake` tests against the stored fixture),
which is the mechanism R3 named and Slice 3 declined for lack of a source.

## Option B — `docs/scenarios/09-global-race-calendar.md` as a secondary source

**What it is.** A 751-line, `[Global]`-qualified career race calendar with per-row turn, slot, tier,
distance, surface, track, fans to enter and fans for first, plus a G1 index, slot counts, a `[Global]`
versus `[JP]` diff, and its own Known gaps section.

**Readiness against the condition the brief set.** D-227/D-228 want each row to carry its own server
qualifier and source date. The file carries both **at document level** (`**Server:** [Global]`,
`**Last Verified:** 2026-09-28`) and per-row *in prose* where it matters: seven regional slots are
dated to the 2026-07-22 arrival, two Longchamp slots are excluded from `[Global]` outright, and nine
rows are marked with a `⚠️` because `[Global]` has no first-place payout curve. A row-level import can
therefore be qualified and dated only by inheriting the file's stamp, which is true for every row the
file says is `[Global]` and **false** for the excluded and undated ones unless the importer reads the
prose flags too. That reading is mechanical but it is a reading, not a column: the second premise in
the brief holds only if the importer takes the caveats with the rows.

**What it unlocks.** The same career slots as option A, with the guide's own words for a reader who
wants to check a number, and it is already tracked in git.

**What stays dark.** Everything option A gets from `races.json` that the tables do not print: no
`instance`/`tid` identity, so a row cannot be reconciled against the export it came from, and a
calendar change upstream shows up as a text edit rather than a hash. The per-character Goal sets it
explicitly refuses to generalise (`:15-16`), the payout curves it marks missing, and the Trackblazer
and Team Race timings it deliberately does not re-paste (`:6`).

## Option C — client capture for Trackblazer free races

**Status: deferred, and still the only way to light up Trackblazer's free-race entry.** No document in
`docs/scenarios/` lists the free-form races a Trackblazer career can enter, and none states which
Grade Point track applies to which aptitude (`KI-15`: the two Trackblazer guides disagree, and neither
names the letter). A capture is the evidence class both questions need, which is why R51's "no new
client strings without a capture or a dated source" still holds.

**What it unlocks.** The free-race writer for Trackblazer, the epithet routes that name free races, and
the third of `KI-15`'s tracks.

**What stays dark.** Everything already decided against it: the capture corpus is a manual step, so a
slice cannot produce it as a side effect, and `KI-17` would remain open because a captured race row
still would not link a finish to a turn.

## What is common to all three

- None of them is available without the schema decision above, so no slice should treat "pick a
  source" as the next actionable step. Picking the key is first.
- Whichever is chosen, provenance lands per row: `source_url`, `snapshot_path`, `fetched_at`,
  `source_timezone`, `is_manual`. A row whose date cannot be stated is not seeded.
- `KI-17` is not unlocked by any source. It is a missing link between `race_entries` and turns, and it
  stays entered-per-`D-270` until someone rules a column.
- A populated calendar makes `docs/scenarios/01`'s guide text and the seeded rows two authorities. D-228
  decides by date: the newer dated source wins, and the app should record which one it used rather than
  reconciling them silently at read time.

## Decision

None taken. The owner's three questions are, in the order that unblocks the rest:

1. Which key shape carries more than one race per month and half (above), and does that need a
   migration now or a later slice?
2. Fetch engine or seeder for the chosen source, given that R3's reason for the fetch engine was the
   absence of a source rather than a rejection of seeders?
3. Which tier label set `scenario_slots.tier` commits to, and whether `tier` and `slot_label` should
   both exist while both can hold `G1`.

This ADR may be cited as evidence that the source question is answered and the schema question is not.
It may not be cited as permission to seed.
