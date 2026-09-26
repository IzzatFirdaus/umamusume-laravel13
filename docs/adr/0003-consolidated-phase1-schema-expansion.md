# ADR-0003: Consolidated Phase 1 schema expansion for Energy, Fans, events and races

Status: **accepted by owner 2026-09-27.** Absorbs the pending items in `ADR-0001` and `ADR-0002`.
Deciders: product owner (approval and US-10 promotion), design consultant (column definitions and consequences)
Supersedes: `ADR-0001` §5 (schema), `ADR-0002` recommendation
Related: `PRD.md` US-3, US-4, US-10, FR-C; `CLAUDE.md` Planner Rules 4, 5, 6

## Owner decisions recorded

1. **US-10 promoted from P2 to P1.** Reason given: a companion tool that cannot manage the race calendar and fan gating does not serve the micromanagement loop the game is built on.
2. **Energy, TurnEvent and Race/Fan tracking bundled into one amendment**, with column names defined here rather than deferred.
3. **Summer Camp corrected by the owner.** The earlier "choose 2 disciplines" instruction was a misremembering; §1.1.2 stands. Screen D is a generic multi-step scenario event, not a hardcoded camp picker (CONSTRAINTS D-135).
4. **The maiden gate is a distinct lock state** from the fan gate, visually and mechanically.
5. **`scenario_races` is generalised to `scenario_slots` with a `kind` discriminator** (owner ruling, 2026-09-27, after the scenario work). Reason: **Trackblazer has no mandatory race goals at all.** A table named for races cannot hold a Grade Point deadline, and the tool must not break on the third scenario. `kind` takes `GoalRace`, `TeamRace`, `GradeDeadline` or `ScriptedEvent`, so one table and one timeline view serve all four scenarios. The section below keeps its original name and columns as the `GoalRace` case; the rename is a schema task, not a redesign, and it is **unfunded work** logged here so it is not lost.
6. **The stat bound moves to 0..2000** (owner ruling, 2026-09-27). `ADR-0002` option B, on the ground that `scenarios.json` carries `hard_caps = 2000` as a real field and the current 0..1200 bound has been rejecting live Global runs since the 2026-07-01 rework. The UI must keep the 1,200 halved-gains line and the scenario ceiling as two visible markers rather than silently raising one number; see `ADR-0002` amendment 2 and `DESIGN.md` §6.5.

## Design principle behind every column below

The schema already stores **absolute per-turn values**, not deltas: `turn_entries.speed` is the Speed the Trainee holds at that turn, which is why an injury drop is representable as 302 then 294 with no extra machinery. Energy and Fans follow the same rule.

**Store the end-of-turn total, not the delta.** Three reasons: it matches the existing convention, so there is one mental model for every numeric column; a delta series cannot answer "what was the Energy at the start of turn 14" without a summation that depends on every prior row being present and correct; and a missing or corrected middle turn silently corrupts every later derived total, whereas an absolute value is wrong only for itself. Deltas remain available as a presentation-layer subtraction, which is where they belong.

## Schema

### `turn_entries` additions

| Column | Type | Null | Rationale |
|---|---|---|---|
| `energy` | `unsignedSmallInteger` | yes | End-of-turn total, 0..100. Nullable because historical runs were logged without it and must not be backfilled with guesses |
| `fans` | `unsignedInteger` | yes | End-of-turn running total. Drives eligibility display |
| `mood` | `string`, cast to `MoodTier` enum | yes | Replaces the free-text `condition` as the structured mood home |

`condition` stays as free text for notes. `mood` is the queryable enum. The overlap is deliberate and temporary: retiring `condition` in the same change would break the existing run views for no functional gain.

**The `MoodTier` case names are blocked.** `UMAMUSUME_REFERENCE.md` §1.1.6 records the Global client strings for the five states as `❌ UNVERIFIED`; the corpus evidences only `GREAT` and `GOOD`. The enum is defined with neutral cases (`Peak`, `Good`, `Normal`, `Poor`, `Worst`) and the display labels stay unset until captured from the client. CONSTRAINTS D-20 and D-138 apply.

### `turn_events` (new)

One row per event the Trainer resolved, whatever fired it.

| Column | Purpose |
|---|---|
| `training_run_id` FK cascade, `turn` | locates the event, same key shape as `turn_entries` |
| `event_type` enum-backed | `Character`, `SupportCard`, `Group`, `Scenario` |
| `source_name` | event title as displayed |
| `choice_index`, `choice_label` | which option was taken, and its text at the time |
| `deltas` json | stat, SP, Energy, Mood and Fan changes **as observed** |
| `support_card_name`, `bond_delta` | nullable, support-card events only |
| `origin_note` | nullable free text, so an event fitting no category is recorded honestly |

`deltas` stores observed outcomes only. `turn_entries` remains the source of truth for absolute values; a mismatch between the two is a defect with a named owner, not a second opinion.

### `scenario_races` (new, reference data)

The fixed calendar. §1.6.1 confirms the scenario dictates the target-race calendar, and `ura-races.json` in the data export carries `fans_needed` and `fans_gain` per row, so this is real, citable data rather than a guess.

| Column | Purpose |
|---|---|
| `scenario_key`, `slot_label` | e.g. `ura-finale` / `Classic Year Late May` |
| `race_name`, `tier` | display name and tier label |
| `fans_needed` | the gate value, shown on locked cells |
| `mandatory` | bool, the Goal flag |
| `maiden_gated` | bool, the conditional Debut/Maiden lock |
| provenance fields | `url`, `fetched_at`, `snapshot_path`, `source_timezone`, per FR-A-4 |

**Provenance is not optional here.** Every cap and `fans_needed` value traces to sources flagged `⚠️ STALE` (Game8 2025-11-21, Kamigame 2024-02-19), and Grand Masters and L'Arc are marked "single-source, not corroborated". The Data Engineer rule is that a fact without provenance is deleted rather than stored, so a seeded calendar row without a source is a rule violation on arrival.

### `race_entries` (new)

What the Trainer actually did with each calendar slot.

| Column | Purpose |
|---|---|
| `training_run_id` FK cascade, `scenario_race_id` FK | the slot |
| `status` enum-backed | `NotOffered`, `Skipped`, `Entered`, `Completed` |
| `placement` | nullable, recorded result |
| `fans_gain` | nullable, observed |

`Skipped` being a stored status is the point: the owner required that skipping be a first-class action, and a first-class action needs a first-class value. A nullable-absent row cannot distinguish "not offered" from "declined", which is exactly the distinction the calendar UI renders.

## What this does not license

- **No race outcome prediction.** `PRD.md` §6.11 remains in force on that point; `ADR-0001` lifted the non-goal for Energy guidance only. Eligibility is arithmetic over stored values and is permitted. Projected placing, win probability and ranked race recommendations are not.
- **No failure percentage.** No source publishes a curve. Risk renders as Safe / Caution / Danger bands.
- **No tier inference from grade codes.** Only 100 = G1 and 400 = OP are client-confirmed. `scenario_races.tier` stores the label as recorded, never a derived value.
- **No support-card or breakthrough cap maths.** §1.3.4 makes a run's true ceiling scenario base plus breakthrough plus deck effects, and neither input is in Phase 1's schema. The cap display discloses what it is not accounting for (D-162).

## Consequences for the validation bound

`ADR-0002` established that the 0..1200 stat bound makes P0 US-3 unable to record a real run. With US-10 now P1 and scenario caps a first-class UI concern, that bound is no longer defensible as-is. Two things must land together with this schema:

1. The per-stat bound becomes **scenario-derived**, with `scenario_races`-style reference data supplying the caps, rather than a flat constant in `StoreTurnEntryRequest`.
2. Until that lands, a rejected value must produce an error naming **the tool's** limitation, never implying a game ceiling (D-31, G-15c).

This is a `StoreTurnEntryRequest` change, and Planner Rule 6 says bounds live in that one place. It stays true; only the source of the number moves.

## Work required before implementation

- `PRD.md`: promote US-10 to P1, add FR-C-6 for Energy and Fans, FR-C-7 for event and race logging, and amend §6.11 to the narrower race-outcome prohibition.
- `CLAUDE.md`: Planner Rule 4 needs the ADR-0001 §2 replacement wording (entered turns plus a declared, versioned constant set). Rule 6 needs the bound-source change above.
- `AGENTS.md`: the Planner Domain Specialist line asserting "no race predictions" needs the same narrowing, and the new columns need their FR citations recorded there.
- Re-verify the stale cap and `fans_needed` sources before seeding.

## Open items this leaves with the owner

| Item | Blocker |
|---|---|
| `MoodTier` display labels | Global client strings unverified; only GREAT and GOOD evidenced |
| Races per turn | "2 or 3" has no source in this repo |
| Senior-year Arima Kinen as a hard requirement | No source in this repo |
| Scenario-specific chrome | The entire 1,160-frame corpus is Unity Cup; there is no visual evidence for the other three scenarios' UI |

---

## Formal record, 2026-09-27

`scenario_slots` is **approved by the owner** and recorded here so the file matches the decision. The
column definitions themselves are not in this document and the implementing agent did not invent them;
the definition still needs to be written down before that column is migrated.

Companion work recorded elsewhere: `ADR-0004` covers the ten aptitude letters and the `scenarios`
reference table, and closes `PRD.md` OQ-4 for those two domains only.
