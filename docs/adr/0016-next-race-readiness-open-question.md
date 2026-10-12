# ADR-0016: Qualitative next-race readiness — OPEN QUESTION, blocked on race requirement data

<!-- ADR-CONTRACT
  decision: open question — no decision is taken or implied; the owner held Slice 3 on 2026-10-01 and the hold is the ruling
  code: not built — nothing was built for Slice 3; KI-45 carries the operational statement of the gap
  superseded-by: —
  amended-by: 0020 (Decision 4 reaffirms the hold; §6.11 stays unchanged for race outcomes)
  open: the data gap below is unclosed and an owner scope ruling does not close it — race distance and surface live only in race_catalog_slots, which is fetch-only and holds 0 rows offline
-->

Status: **OPEN QUESTION. No decision is taken or implied here.** Slice 3 was authorized and then held by the
owner on 2026-10-01 after the pre-flight data check returned a blocker; the hold is the ruling, the three
candidate shapes are not. `PRD.md` §6.11 is **not amended by this document** and stands exactly as written:
no race simulation, no prediction engine, no race-day snapshots. **No slice may cite this ADR as permission to
build the surface it describes.**

> **Dated note 2026-10-04:** the owner revisited race prediction while ruling on the Trainer Desk 2.0 program
> (`ADR-0020`) and **kept this hold**. The blocker is the data gap measured below, which an owner scope ruling
> does not close.

## Why this ADR exists rather than a decision

Slice 3's brief specified a scoring model producing per-stat marks from the run's current stats plus the
trainee's aptitudes, and instructed that the data question be settled first. That check was run. It failed,
and it failed on the side of the comparison that has no substitute. Writing up the result is the deliverable;
choosing among the repairs is not this slice's call, and an ADR that picked one would be a decision wearing a
record.

## The measurement

`race_catalog_slots` is the **only** table in the schema carrying a race's distance or surface, and
`scenario_slots` — the table a run actually joins to — has no distance or surface column at all. So there is
no path from an upcoming race to "which distance band and running style does this ask of her", which is the
comparison the marks express.

**The gap is not incidental and has a named cause.** The NULL tiers are R75's doing: Slice 15 ruling R75
nullified any tier the export's source could not corroborate per row, on the reasoning that a silently
mislabelled grade is worse than an absent one. The strictness was correct then and it is what removes the
vocabulary today. The non-null set also has no `Pre-OP` and no `EX`, while `config('scenarios.php')`'s
`grade_point_by_grade` carries `Pre-OP` and D-153 records `EX` as a sixth label — so config and the seeded
table do not share a grade vocabulary.

**And it is fetch-only, so no seed closes it offline.** No seeder and no command populates that table. It
arrives through `uma:fetch` and `GametoraRaceCatalogParser` only, and its source has no `seed_file` key, so
`migrate --seed` cannot reproduce it. The upstream bodies are on disk, so the data exists; the offline path to
it does not.

## What is *not* the problem

Two candidate objections were checked and both dissolved, and recording that matters because a future reader
should not re-litigate them:

- **Aptitude is fully available.** All trainees carry all ten letters with no NULL cells, and the grid renders
  them as the export's letters A-G. The trainee side of the comparison is complete.
- **The fan gate is available too**, non-null on every slot. **The maiden gate is the caution:** it is non-null
  on every slot but **true on none**, so it is a column that evaluates correctly and never fires. Column
  presence is not signal presence, and that distinction is why this bullet list exists — the row looked closed
  until the non-zero count was taken.
- **○/◎/△/× are not an existing repo vocabulary being disturbed.** Nothing uses those glyphs for aptitude or for
  anything else; aptitude is letters. The one client-evidenced occurrence is inside a skill name quoted as
  source data, which is a different namespace. Adopting the marks would introduce a second homegrown scale
  beside the one already in `config('scenarios.php')` `grade_banding`, which is explicitly provisional with its
  step printed next to the badge and its sourcing recorded as a blocking item. That precedent is the right shape
  for any such scale; it is not a licence to skip sourcing.

## The three candidate shapes, recorded without choosing

The owner selected "hold" rather than any of these, so they are set out for the eventual decision and no
further.

1. **Seed first, then predict.** Give the race-catalog source a `seed_file` and a seeder so `race_catalog_slots`
   exists offline, then build the marks against real distance and surface.
2. **Build on what is populated.** Deliver the fan gate and the Trackblazer grade-point trajectory, each a pure
   function of entered turns and race entries. The maiden gate is **excluded** from this shape: it is true on
   zero slots, so shipping it would render a rule that can never fire and read as coverage the source does not
   provide. Render absence by name where distance and surface would be needed.
3. **Build the marks anyway.** Rejected in advance by the implementing side and not recorded as viable: the
   requirements would have to be invented, which fails the no-fabricated-claims rule, the pipeline's
   provenance floor, and the ban on unexplained recommended numbers. A panel that renders a grade the source
   cannot support is worse than no panel, because it looks like knowledge.

## What closes this

One of: a populated `race_catalog_slots` reachable offline; or a source that states each scenario goal race's
distance band and surface on the row the run references; or an owner decision to scope the panel to the gates
that need neither. Until then the honest state is US-10 as written today, which says predictions "remain
deferred per §6.11" — a requirement this repository currently satisfies, and would break to satisfy the brief.

## Consequences of the hold

- **No schema, no API, no panel, no ADR-0016 decision.** Nothing was built for Slice 3.
- `PRD.md` §6.11 untouched; `ADR-0001`'s partial lift unchanged; US-10 unchanged.
- **KI-45** carries the operational statement of the gap, so a later session finds it before writing code
  rather than after.

## Links

- `PRD.md` §6.11 and US-10; `ADR-0001` (the partial lift, unchanged), `ADR-0009` (slot seeding), `ADR-0003`
  (race tracking), `ADR-0020` (reaffirms this hold)
- `config/scenarios.php` (`grade_objectives`, `grade_point_by_grade`, `grade_banding`)
- `docs/UMAMUSUME_REFERENCE.md` §1.2.6 and §1.3; `docs/design-research/CONSTRAINTS.md` D-153
- `docs/design-research/verification/slice-15-2026-09-29.md` §2 (R75); KI-45
