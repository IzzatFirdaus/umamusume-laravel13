# Unity Cup capture: proposed schema for team rank, spirit bursts, team races and the resource strip

Status: **Proposal. Not authorized, no migration written, no column created.** It answers
`SCREEN_SPEC.md` §7-6, which calls the gap "Implementation Gap blocked on a schema proposal with PRD
citation (owner backlog item 2)", and `docs/UIX-AUDIT-TRAINING-RUNS.md` R-2 and O-6/O-7. Under
`AGENTS.md` escalation 1 a schema change is the Architect's decision within PRD scope, and anything
outside it goes to the human owner; this document is the input to that decision, not the decision.

## The defect being answered

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

## What the PRD already authorizes, and what it does not

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

## What the domain already defines, so the proposal does not invent vocabulary

`config/scenarios.php` is the single source for the scenario's shape and already carries the value
sets. A capture column validates against these rather than restating them, the way
`StoreTrainingRunRequest::scenario()` validates against the composition matrix (D-240):

| Thing | Where it is already declared | Values |
|---|---|---|
| Team rank ladder | `:139-145` `team_rank_ladder` | `G, F, D, E, C, B, A, S` grouped into facility levels 1..5, with `:146` recording that **S+** sits above S and grants a second hint |
| Spirit burst state | `:138` `spirit_burst_states` | `chargeable, charged, held, spent, extreme_ready, extreme_spent` |
| Team race shape | `:119-137` `team_race` | `occurs_every_months: 6`, `opponent_count: 3`, three named opponents keyed by `tier`, `circles_guidance: 3`, `loss_lowers_league_rank: true`, `loss_retryable_with_alarm_clock: true` |

Two consequences worth stating out loud. A rank is a **letter plus the S+ case**, so a plain
`string` column with a config-derived rule is enough and a DB enum is barred anyway (`AGENTS.md`:
no DB-level enum columns). And `:133` says losing lowers league rank while `:130-132` says circles are
a safety margin rather than a win condition, which is why a stored league placement must be a reading,
not something derived from the round results the tool holds.

## Proposal

Four changes, split by the question R-2/O-6 actually turns on: **is the missing thing a column or a
row?** Three of the five report figures are columns on existing tables; the fourth, the rounds, is
rows in a table that already exists.

### 1. `turn_entries`: two new nullable columns (per-turn readings)

| Column | Type | Why it is a turn column |
|---|---|---|
| `team_rank` | `string(2)` nullable | The client shows TEAM RANK in the resource strip every turn and it moves during a career. `training_runs.team_rank` would store only the last reading and make the strip's "not yet recorded" state impossible to distinguish from "recorded once at the end". Precedent: `energy`, `mood`, `fans` are end-of-turn totals on this table. |
| `league_position` | `unsignedTinyInteger` nullable | The report's `8th`. Same argument as `team_rank`: it changes, and `:133` documents that a loss moves it. Not the same thing as `race_entries.placement`, which is a finishing position in one race. |

Validation at the boundary, in `StoreTurnEntryRequest`: `team_rank` against the letters read out of
`config('scenarios.scenarios.unity_cup.team_rank_ladder')` plus `S+`; `league_position` a positive
integer with no upper claim, since no source publishes the league size. Both stay nullable, and a run
that has not recorded one renders `N/A` with its `title`, which is the rule the strip already follows
(D-220), so the proposal does not change what a missing value looks like. It makes the value
enterable.

### 2. `turn_entries`: burst counts, or one counts table (open question, see below)

The report gives two cumulative figures, `6 bursts` and `5 extremes`. Two honest shapes:

- **(a) two int columns** `spirit_bursts` / `spirit_bursts_extreme` on `turn_entries`, read as
  end-of-turn totals like `fans`.
- **(b) a `turn_events` payload**, the mechanism a recorded failure already uses (`ADR-0003`): an
  observed event with deltas and a note, keyed by turn, with no new column on the turn row.

(a) is what the strip needs (a per-turn total to print). (b) is what the burst *state* vocabulary in
`:138` suggests if a Trainer wants to say which burst is in which of the six states rather than how
many were used. This is the item to decide before writing anything, and the existing
`TurnEvents/` payload classes are the precedent for (b).

### 3. Team race rounds: rows, not columns

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

### 4. The resource strip

`TEAM RANK` and `SPIRIT BURSTS` cells stay as they are once 1 and 2 exist: they read the latest
recorded turn value and print `N/A` with a `title` when none is. R-2's alternative, "drop the two cells
from the strip until they can be set", is the smaller change and is *not* proposed here, because it
removes a cell the scenario's own config declares as a widget
(`:92` `widgets: [turn, energy, fans, team_rank, spirit_bursts]`). If the owner would rather ship the
deletion than the columns, that is the cheaper resolution and §7-6 closes as Deferred.

## Open questions for the owner

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

## What this document deliberately does not contain

No migration, no column created, no model change, no seeder. The repo rule for a schema-hungry finding
is proposal-then-ruling, and `CONSTRAINTS.md` outranks this file: nothing here weakens a gate, and no
item in `docs/UIX-AUDIT-TRAINING-RUNS.md` is closed by writing it.
