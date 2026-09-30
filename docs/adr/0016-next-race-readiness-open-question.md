# ADR-0016: Qualitative next-race readiness — OPEN QUESTION, blocked on race requirement data

Status: **OPEN QUESTION. No decision is taken or implied here.** Slice 3 was authorized and then held by
the owner on 2026-10-01 after the pre-flight data check below returned a blocker; the hold is the ruling,
the three candidate shapes are not. `PRD.md` §6.11 is **not amended by this document** and stands exactly
as written: no race simulation, no prediction engine, no race-day snapshots. Nothing in this file lifts it,
and no slice may cite this ADR as permission to build the surface it describes.

Date: 2026-10-01
Deciders: product owner (held the slice), implementing agent (measurement and record)
Relates to: `ADR-0001` (lifted §6.11 *in part*, for Energy guidance only — that partial lift is unchanged
by this file), `ADR-0009` (scenario slot seeding), `ADR-0003` (race tracking), `PRD.md` §6.11 and US-10,
`docs/UMAMUSUME_REFERENCE.md` §1.2.6 and §1.3, `config/scenarios.php`, **KI-45**

## Why this ADR exists rather than a decision

Slice 3's brief specified a scoring model producing ○/◎/△/× per stat from the run's current stats plus the
trainee's aptitudes, and instructed that the data question be settled first: *"Data: race requirements —
check `scenario_slots` and `config/scenarios.php` first."*

That check was run. It failed, and it failed on the side of the comparison that has no substitute. Writing
up the result is the deliverable; choosing among the repairs is not this slice's call, and an ADR that
picked one would be a decision wearing a record.

## The measurement

Taken against `storage/app/backups/uma-backup-20260930-155251.sqlite`, which is a peer session's seed of the
shared development database and the most-populated state available on this machine.

| What the model needs | Where it lives | Populated? |
|---|---|---|
| Race distance, distance band, surface | `race_catalog_slots` (`distance`, `distance_band`, `surface`, `grade_code`) | **0 rows** |
| The race a run is preparing for | `race_entries.scenario_slot_id` → `scenario_slots` | 0 rows / 296 rows |
| Tier of that race | `scenario_slots.tier` | **141 of 296 NULL**; non-null only G1 34, G2 42, G3 76, OP 3 |
| Fan gate | `scenario_slots.fans_needed` | **296 of 296** non-null |
| Maiden gate | `scenario_slots.is_maiden_gated` | non-null on 296, but **0 rows true** — computable, never fires |
| Grade-point targets | `config('scenarios.php')` `grade_objectives`, `grade_point_by_grade` | present, Trackblazer only |
| Trainee aptitudes (all ten letters) | `umamusume.aptitude_*` | **67 of 67** complete |

`race_catalog_slots` is the **only** table in the schema carrying a race's distance or surface, and
`scenario_slots` — the table a run actually joins to — has no distance or surface column at all. So there
is no path from an upcoming race to "which distance band and running style does this ask of her", which is
the comparison the marks express.

**The gap is not incidental and has a named cause.** 141 NULL tiers are R75's doing: Slice 15 ruling R75
(`docs/design-research/verification/slice-15-2026-09-29.md` §2) nullified any tier the export's source could
not corroborate per row, on the reasoning that a silently mislabelled grade is worse than an absent one. The
strictness was correct then and it is what removes the vocabulary today. Note also that the non-null set has
no `Pre-OP` and no `EX`, while `config('scenarios.php')`'s `grade_point_by_grade` carries `Pre-OP` and
D-153 records `EX` as a sixth label — so config and the seeded table do not share a grade vocabulary.

**And it is fetch-only, so no seed closes it offline.** `grep -rln "race_catalog_slots" database/seeders/
app/Console/Commands/` returns nothing: no seeder and no command populates that table. It arrives through
`uma:fetch` and `GametoraRaceCatalogParser` only, and its source has no `seed_file` key, so
`migrate --seed` cannot reproduce it. The peer's re-seed produced 296 `scenario_slots` and **0**
`race_catalog_slots` for exactly that reason. The upstream bodies are on disk (`research-scratch/data/json/`
holds `races.json` 125,292 B, `race_instances.json` 213,813 B, `racetracks.json` 107,466 B), so the data
exists; the offline path to it does not.

## What is *not* the problem

Two candidate objections were checked and both dissolved, and recording that matters because a future reader
should not re-litigate them:

- **Aptitude is fully available.** All 67 trainees carry all ten letters, with **0 NULL cells across the ten
  columns**, and `x-aptitude-grid` renders them as the export's letters A–G. The trainee side of the
  comparison is complete.
- **The fan gate is available too**, non-null on 296 of 296. The maiden gate is the caution: `is_maiden_gated`
  is non-null on all 296 but **true on none**, so it is a column that evaluates correctly and never fires.
  Column presence is not signal presence, and that distinction is why this bullet list exists — the row
  looked closed until the non-zero count was taken.
- **○/◎/△/× are not an existing repo vocabulary being disturbed.** Nothing in `resources/views` uses those
  glyphs for aptitude or for anything else; aptitude is letters. The one client-evidenced occurrence is
  inside a skill name quoted as source data ("Runner's Tricks ◎"), which is a different namespace. Adopting
  the marks would introduce a second homegrown scale beside the one already in `config('scenarios.php')`
  `grade_banding`, which is explicitly `'provisional' => true` with its step printed next to the badge and
  its sourcing recorded as a blocking item. That precedent is the right shape for any such scale; it is not
  a licence to skip sourcing.

## The three candidate shapes, recorded without choosing

The owner selected "hold" rather than any of these, so they are set out for the eventual decision and no
further.

1. **Seed first, then predict.** Give the race-catalog source a `seed_file` and a seeder so
   `race_catalog_slots` exists offline, then build the marks against real distance and surface. Turns Slice 3
   into 3a (data) and 3b (panel). Cost: a committed race fixture of roughly 340 KB, and it must edit
   `config/uma.php`, which is currently entangled with a concurrent session's uncommitted `seed_file` work
   (see `SESSION-CONSOLIDATION-2026-09-30.md` §6).
2. **Build on what is populated.** Deliver the fan gate and the Trackblazer grade-point trajectory, each a pure
   function of entered `turn_entries` and `race_entries`, so Planner Rules 1, 4 and 5 hold and ADR-0001's
   partial lift is not stretched. The maiden gate is **excluded** from this shape: `is_maiden_gated` is true
   on 0 of 296 slots, so shipping it would render a rule that can never fire and read as coverage the source
   does not provide. Render absence by name where distance and surface would be needed. Cost: it is not the
   per-stat mark panel the brief describes.
3. **Build the marks anyway.** Rejected in advance by the implementing side and not recorded as viable: the
   requirements would have to be invented, which fails `AGENTS.md`'s "no fabricated claims", the pipeline's
   "a fact without provenance is deleted, not stored", and Planner Rule 5's ban on unexplained recommended
   numbers. A panel that renders a grade the source cannot support is worse than no panel, because it looks
   like knowledge.

## What closes this

One of: a populated `race_catalog_slots` reachable offline; or a source that states each scenario goal race's
distance band and surface on the row the run references; or an owner decision to scope the panel to the gates
that need neither. Until then the honest state is US-10 as written today, which says predictions "remain
deferred per §6.11" and lists "No prediction engine or simulation" as an acceptance criterion — a
requirement this repository currently satisfies, and would break to satisfy the brief.

## Consequences of the hold

- **No schema, no API, no panel, no ADR-0016 decision.** Nothing was built for Slice 3.
- `PRD.md` §6.11 untouched; ADR-0001's partial lift unchanged; US-10 unchanged.
- **KI-45** carries the operational statement of the gap, so a later session finds it before writing code
  rather than after.
