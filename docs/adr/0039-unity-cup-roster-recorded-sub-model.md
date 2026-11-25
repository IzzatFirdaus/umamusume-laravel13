# ADR-0039: The Unity Cup team roster is a recorded sub-model, never the six-card deck

<!-- ADR-CONTRACT
  decision: accepted (owner delegation 2026-11-18, "proceed with every owner gated tasks") — the twenty characters who train alongside the trainee are a recorded sub-model: `roster_members` (one row per member per run), written by its own controller, and the Cockpit's `roster` read-out is that table; the six `deck_slots` stay the support-card deck (ADR-0014) and the two are never merged
  code: built — migration 2026_11_24_120000_create_roster_members_table (with down()); App\Models\RosterMember (booted() saving guard, MAX_MEMBERS = 20, ACES_PER_DISTANCE = 1), App\Enums\RosterDistance, App\Http\Controllers\Career\RosterMemberController, App\Http\Requests\Career\StoreRosterMemberRequest, TrainingRun::rosterMembers(); CockpitController::rosterSection() reads the recorded roster; tests/Feature/CockpitRosterTest.php, RosterSeederReRunTest.php, RosterMemberTest.php, RosterDoesNotTouchTeamRankTest.php, RosterMembersForeignKeyRepairTest.php
  superseded-by: —
  amended-by: —
  open: the member's per-skill list (A-0.14 / KI-126) stays unrecorded — this ADR stores the roster and its five columns, not a skill table; the twenty-slot roster carries a `20 / 20` counter whose denominator is the client's own display figure, not a row this tool must fill, so a short roster renders the count against 20 and never a fabricated member
-->

Status: **accepted** (owner delegation 2026-11-18, "proceed with every owner gated tasks").
The work landed at `2026_11_24_120000_create_roster_members_table` before this record; the owner's
delegation is the ruling and this ADR is the write-up that `DECISIONS-AND-DEPENDENCIES.md` D-9 and
`PRODUCT-AND-CAPABILITY-ROADMAP.md` §4.4 already said the slice was owed.

## Context

`DECISIONS-AND-DEPENDENCIES.md` D-9 and `PRODUCT-AND-CAPABILITY-ROADMAP.md` §4.4 framed the roster
as "a derived read-out of the six-card deck, with no column and no form field", and gated any
persisted sub-model on an ADR or a terminal ruling. Measurement of the source split the fact in two.
§1.7-1.9 of `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` reads a Team Info panel that says
`Members: 20 / 20` and a Team tab that lays the roster out as five distance columns — Sprint, Mile,
Medium, Long, Dirt — with an ACE slot plus two more in each. §1.9 also records that the deck's six
support-card characters appear *as teammates* while the rest arrive as Guest R cards tagged
`[Tracen Academy]`. The deck is therefore a subset of the roster, never a stand-in for it, and
`ADR-0014`'s six-card deck is a different fact that stays where it was.

`ADR-0029` A-0.13 had already filed the counter gap ("The 20/20 counter counts a stored roster the
tool does not hold (the derived deck roster is 6, not 18)") and D-6 of that ADR named the missing
PRD anchor as the open item. This ADR is the ruling D-6 asked for: a roster table at run grain.

## Decision

**The roster is a recorded sub-model, `roster_members`, one row per member per run.** It stores
what the client shows off each member's card: `name` (the one required field — a row with no
identity is not a member), nullable `umamusume_id` (the trainee and the deck characters resolve to
catalogue rows; the Guest R cards §1.9 names do not all exist here, so a recorded guest keeps her
name and no link), the member's own grade letter (`G`..`S`, the same vocabulary as aptitudes), five
nullable stat figures, a nullable `distance_assignment` in `RosterDistance` (Sprint/Mile/Medium/Long/
Dirt — Dirt is present here and deliberately not in `BuildTargetPayload::DISTANCE_BANDS`, because a
team race is keyed off `team_race.categories`, not off a training target), and the `is_ace` /
`is_trainee` flags. `position` gives the Team tab a stable order.

**Three rules are enforced at the model boundary, not the migration.** SQLite renders no SQL for a
`->check()` column modifier, so a DB-level constraint would be a no-op; `RosterMember::booted()`
holds the 20-member ceiling (`MAX_MEMBERS`, the client's own `20 / 20`) and the one-ACE-per-column
rule (`ACES_PER_DISTANCE`, the shape the Team tab draws), and `StoreRosterMemberRequest` catches
both as validation errors before the model throws. `is_trainee` marks the row that is the trainee
herself, because §1.8's ACE row names her as the Long ACE and the client's count includes her.

**The Cockpit read-out is the recorded roster.** `CockpitController::rosterSection()` reads
`$run->rosterMembers` (ordered by `position`, then `id`) and returns `{members, empty, unrecorded}`.
A run with no roster rows — every legacy run — reports `unrecorded: true` and renders no members,
never a roster fabricated from the deck. The deck stays `deck_slots` under `ADR-0014` and is untouched.

**The write surface is its own controller.** `RosterMemberController` (store/update/destroy) is
nested under the run, so a member posted to another run's roster is a 404 before any write. It never
reads or writes `training_runs.team_rank` (`ADR-0034`); that column is the run's manually recorded
league standing and this controller leaves it alone.

**Every figure is nullable, because unrecorded is not zero (D-220).** A member added by name has no
stat line yet, and a fabricated figure would be a claim about a record that does not exist. The
`20 / 20` counter prints the recorded count against the client's 20 and names the absence when
nothing is stored; it never invents members to reach twenty.

## What this does not license

- **No merge with the deck.** `deck_slots` is the support-card deck (`ADR-0014`); `roster_members`
  is the team roster. A surface that needs both reads both, and neither is ever rendered as the other.
- **No per-skill roster table.** A-0.14 / KI-126's `team_roster_member_skill` stays unrecorded; this
  ADR stores the member's five stats and grade, not her skill list. Re-opening A-0.14 needs its own
  slice and its own schema bundle.
- **No back-fill from the deck.** Writing `roster_members` for a legacy run from its `deck_slots`
  would record the deck's six as if they were the twenty, which is the conflation this ADR retires.
- **No new `20 / 20` counter semantics.** The denominator is the client's display figure, not a
  row this tool holds to twenty; a run with three recorded members shows `3 / 20`, not a padded roster.

## Links

- `docs/roadmap/DECISIONS-AND-DEPENDENCIES.md` D-9 (the ruling this closes)
- `docs/roadmap/PRODUCT-AND-CAPABILITY-ROADMAP.md` §4.4 (the capability row this retires)
- `docs/research/flows-career-workspace.md` §1.7-1.9 (the measured gap)
- `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` §1.8-1.9 (Team Info `20 / 20`; five columns; Guest R)
- `docs/scenarios/02-unity-cup.md:170` (the one-to-three-per-distance rule)
- `app/Models/RosterMember.php`, `app/Enums/RosterDistance.php`, `TrainingRun::rosterMembers()`
- `ADR-0014` (the six-card deck, untouched), `ADR-0029` A-0.13/D-6 (the counter gap this closes),
  `ADR-0034` (the Team Rank column this never touches), `ADR-0025` (per-run measurement precedent)
- `tests/Feature/CockpitRosterTest.php`, `RosterSeederReRunTest.php`, `RosterMemberTest.php`,
  `RosterDoesNotTouchTeamRankTest.php`, `RosterMembersForeignKeyRepairTest.php`

## Ledger

- 2026-11-24 · **shipped** — the migration, model, enum, controller, Form Request and Cockpit
  read-out landed, with `CockpitRosterTest` green. `DECISIONS-AND-DEPENDENCIES.md` D-9 and
  `PRODUCT-AND-CAPABILITY-ROADMAP.md` §4.4 were updated to DONE in the same pass; the A-0.14 per-skill
  table stays open (KI-126).
- 2026-11-24 · **recorded** — drafted as `ADR-0039` under the owner delegation of 2026-11-18, so the
  ruling's date and this record's date differ by six days. Nothing in the decision changed; the ADR
  adds the reciprocal links `ADR-0029` and `ADR-0034` did not yet carry to the roster.