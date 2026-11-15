# R6-48 review prep — `team_rank` on the Cockpit Team panel

**Source of the finding:** doc `[Rosy_Dreams]Rice_Shower_Unity-Cup.md` §1.8 line 275 records `Team Rank | S (five stars)` for the seeded Rice Shower run; the Cockpit's Team panel today renders `Team rank | not recorded` (absence, D-220) because nothing populates it. R6-48 asks: record `team_rank` per run so the panel can show the stored letter instead of the absence.

## Schema section

**Current state (measured, not assumed).** `team_rank` is **not a column** on `training_runs`. `PRAGMA table_info(training_runs)` carries no `team_rank` column (verified 2026-11-15). The rank letter rides `turn_events` as a `TeamRankPayload` (`app/Models/TurnEvents/TeamRankPayload.php`), letters `['G','F','E','D','C','B','A','S','S+']` — `S+` accepted, deliberately outside `team_rank_ladder` (sits above S, grants a second hint, no facility level to map to).

`CockpitController::headerSection` (`app/Http/Controllers/Career/CockpitController.php:532`) passes `'team_rank' => $team['rank']` from that payload; `resources/js/pages/Career/Cockpit.vue:549` reads `props.header.values['team_rank']`; `TeamRankLadderPanel.vue` draws it. Null → absence row; a recorded letter renders the panel.

**Review question.** Add `team_rank` to `training_runs` as:
- `nullable string` with vocabulary validation (`G`–`S`, `S+`) at the model/request boundary, or
- `nullable enum` with `S+` documented as excluded from `team_rank_ladder` mapping.

`S+` is a valid league letter (TeamRankPayload accepts it) but per-stat grades explicitly reject `S+` (`CockpitTeamPanelTest:154-161`: "S+ is overall team rank only"), so any column vocabulary must include `S+` while the per-stat path excludes it. Doc §1.8 records `S` for this run; no source contradicts `S+` as a possible stored value.

## Seeder section

**Proposed change (read-only in this session — implementation not authorized):** one-line `'team_rank' => 'S'` addition inside `$model->update([...])` in `RosyDreamsRiceShowerSeeder::run()` (`database/seeders/RosyDreamsRiceShowerSeeder.php:396`), matching doc §1.8 line 275 (`Team Rank | S (five stars)`).

**Blocker flagged for review:** `team_rank` is not a column, so `$model->update(['team_rank' => 'S'])` would fail mass assignment today. Two paths:
1. Migration adds `team_rank` column first, then seeder line lands on it; or
2. Seeder records `team_rank` as a turn event (`TeamRankPayload` on `turn_events`) instead of a column — no schema change, matches today's write path.

Commit message references doc §1.8 line 275; seeder comment cites doc section only (lines drift).

## Cockpit section

**Component:** `resources/js/components/scenario/TeamRankLadderPanel.vue` — registered under `team_rank_ladder`, gated on `scenario.panels.team_rank_ladder.on`. One component, one row.

**Absence row:** `Team rank | not recorded` renders when `team_rank` is null (D-220). Confirmed live on run 35's Cockpit (zero-turn run: `team_rank` absent → absence row, no console errors beyond BO-002).

**Populated state:** letter + stars per doc §1.8 (`S (five stars)`). `S+` renders as a letter; the ladder mapping skips it (no level), so the panel prints the letter without a level hint — the gauge must not invent one.

## Test surface

| Test file | Coverage today | Gap for R6-48 |
|---|---|---|
| `tests/Feature/CockpitTeamPanelTest.php` | Team identity (name/motto/placement/wins), per-stat grades incl. `S+` rejection | No `team_rank` letter case — add one (recorded letter reads back through Cockpit; absence still prints) |
| `tests/Feature/HistoricalRunImportTest.php` | What `ImportHistoricalRun` stores per header | If seeder writes `team_rank` as turn event, add a case pinning the TeamRank turn event |
| `tests/browser/run-import.spec.ts` | Browser round trip of the import | No `team_rank` case |

Whether `CockpitTeamPanelTest` needs the `team_rank` case and whether the seeder change needs `HistoricalRunImportTest` coverage are review calls.

## Risks

- **`S+` scope (design constraint, not a risk):** `TeamRankPayload::LETTERS` correctly includes `S+` and `team_rank_ladder` correctly excludes it. Any schema change must preserve both. A per-stat field must reject `S+`; an overall-rank field may accept it. Documented in the reference at §1.8; must not be collapsed.
- **Two write paths:** rank letter today arrives via turn event (`UnityCupEditor` → `runs.update`); adding a column creates a second place the letter can live. Review must pick one owner so the header strip and the Team panel cannot disagree (CockpitController:528-531 comment).
- **Slice boundary:** R6-48 is one column/payload + one seeder line + one test case. If review finds team-state work (league placement, roster, burst counters) incomplete underneath it, R6-48 folds into that larger slice instead of shipping alone.

## Decision needed (owner)

1. Standalone slice → migration (if column path) + seeder line + test case, then implement.
2. Fold into larger team-state work → R6-48 joins the queue, not its own slice.
3. Needs owner input on schema shape (`string` vs `enum`, `S+` handling) → surface to owner; implementation stays deferred.

## Decision (owner ruling, 2026-11-15) — GATE CLEARED

**Ruling: add the column** (`nullable string('team_rank')`, vocabulary `G,F,E,D,C,B,A,S,S+` validated at the
model boundary, not by a DB enum). The owner's reasoning: the reference doc §1.8 explicitly calls the Team
Rank a "status readout, not a performance record" — it is the *current* state of the run, not a per-turn
history — so event-sourcing one letter through `turn_events` is over-engineering that would also make the
Cockpit panel resolve the current letter from the event stream. The turn-event path stays for the
`UnityCupEditor` write route; the column is the stored current state the panel reads.

`S+` remains accepted by the column and excluded from `team_rank_ladder` mapping (both preserved; see the
`S+` scope risk above). Implementation lands as Phase 2: migration (Slice 2.1), seeder line (Slice 2.2),
Cockpit display (Slice 2.3).

Nothing in this document changes code; all sections are read-only review material.
