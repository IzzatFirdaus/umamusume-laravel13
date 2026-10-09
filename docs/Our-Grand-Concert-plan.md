# Our Grand Concert — Incremental Implementation Roadmap

**Project:** Trainer Desk 2.0  
**Scenario:** Our Grand Concert  
**Purpose:** Incrementally complete the existing Our Grand Concert implementation without speculative mechanics, duplicate architecture, broad refactors, or unverified game data.

---

## Implementation Principle

Our Grand Concert is implemented as a sequence of narrow, independently verifiable slices.

Every slice follows:

> **Evidence → smallest truthful representation → canonical lifecycle → UI → tests → stop**

A roadmap item is **not** permission to implement every mechanic implied by its name.

At every boundary:

- Read the authoritative project documentation first.
- Search the existing codebase before creating new architecture.
- Prefer existing models, TurnEvents, controllers, requests, capabilities, and UI surfaces.
- Do not invent unsupported game mechanics.
- Do not infer numeric values from external/community sources when project evidence marks them unverified.
- Do not create duplicate models, tables, endpoints, stores, or frameworks.
- Keep observed facts separate from derived state and recommendations.
- Stop when the assigned slice is complete.
- A slice may split, collapse, be deferred, or conclude with a documented exclusion if evidence does not justify implementation.

### Evidence hierarchy

When implementing scenario mechanics, distinguish:

1. **Authoritative and verified** — safe to implement/render.
2. **Observed in the application but not formally documented** — may be represented only where the observation itself is trustworthy.
3. **Documented as unverified** — do not render or calculate as fact.
4. **Inferred/community/external mechanics** — do not silently promote to project truth.

---

# Phase A — Performance

## Slice 1 — Baseline Grand Concert Audit

**Status: COMPLETE**

Establish the current implementation state before making changes.

### Scope

Audit:

- scenario registration;
- scenario selection;
- trainee selection;
- training target;
- inheritance/Legacy;
- support deck;
- preflight;
- career state;
- Performance;
- Lessons;
- Songs;
- Live Bonuses;
- Promotional Lives;
- Grand Concert/finale;
- Trainer Advisor;
- Career Report;
- veteran/Legacy handoff.

Identify existing architecture, missing pieces, known defects, disabled panels/capabilities, and explicit documentation boundaries.

### Outcome

Created the incremental implementation map and established that Grand Concert functionality must be completed in narrow slices rather than through a broad rewrite.

---

## Slice 2 — Grand Concert Finale Race Catalog Correction

**Status: COMPLETE**

### Scope

Correct the Grand Concert finale race catalog scenario key:

```text
final_live => our_grand_concert
```

instead of the incorrect:

```text
final_live => grand_concert
```

Verify the resulting catalog identity and ensure there is one valid finale row for the scenario.

### Additional work

- Investigated the orphaned engine-owned catalog row.
- Backed up the row before removal.
- Verified it was not referenced by race entries.
- Removed only the incorrect duplicate/orphan.
- Revalidated the race planner's scenario-scoped lookup.

### Outcome

Grand Concert finale catalog data is correctly associated with `our_grand_concert`.

Commit:

```text
480b711716b0756443a3f5e3aeac3a7f879e3abc
fix(parser): correct Grand Concert final scenario key to our_grand_concert (KI-71)
```

---

## Slice 3 — Performance Domain Foundation

**Status: COMPLETE**

Establish Performance as a typed scenario observation without inventing a Performance balance system.

### Authoritative Global terminology

The verified Global client terms are:

- Dance
- Passion
- Vocals
- Visuals
- Composure

Do **not** substitute the GameTora/JP-derived term "Mental".

### Architecture

Implemented:

```text
app/Enums/PerformanceType.php
app/Models/TurnEvents/PerformancePayload.php
app/Models/TurnEvent.php
tests/Feature/PerformanceFoundationTest.php
```

Payload:

```json
{
  "type": "Dance",
  "delta": 12
}
```

`delta` is signed:

- positive = Performance acquired;
- negative = Performance spent.

### Explicitly not implemented

- starting values;
- current totals;
- caps;
- acquisition formulas;
- spending costs;
- conversion formulas;
- Lesson costs;
- Song costs;
- Hype;
- Live Bonus;
- stat mappings.

Performance TurnEvents are observations, not an absolute balance.

---

## Slice 4 — Performance Turn-Write Integration

**Status: COMPLETE**

Wire Performance observations into the canonical turn-writing lifecycle.

### Scope

Extend the existing:

```text
runs.turns.store
```

path rather than creating a Performance endpoint.

Use:

```text
performance[type]
performance[delta]
```

and persist the observation as the existing Scenario TurnEvent type.

### Constraints

- Existing turn transaction remains authoritative.
- No scenario-specific duplicate write path.
- No automatic calculation.
- Numeric strings from browser forms remain valid and are cast appropriately.
- Empty Performance pair produces no event.

---

## Slice 5 — Performance Read-Side Integration

**Status: COMPLETE**

Expose recorded Performance observations through the canonical `TrainingRun` read path.

Implemented:

```text
TrainingRun::performanceObservations()
```

Output is deterministic and contains:

```text
turn
event_id
type
delta
```

### Explicit boundary

The method does **not**:

- sum Performance;
- derive a current Performance balance;
- infer starting values;
- calculate costs;
- calculate caps.

It exposes the recorded observations only.

---

## Slice 6 — Trainer-Facing Performance Observation Input

**Status: COMPLETE**

Add Performance observation input to the actual committing turn-entry surface.

### Surface

```text
resources/js/pages/Runs/Show.vue
```

using the existing GuidedStep / turn-entry architecture.

### Capability

Performance input is enabled through:

```text
performance_input => true
```

for `our_grand_concert`.

It is deliberately not a new panel.

### UI

Fields:

- Performance type;
- signed Performance change.

Exact options:

- Dance
- Passion
- Vocals
- Visuals
- Composure

No default selection.

No invented min/max.

No calculated total.

### User-facing semantics

Positive values represent Performance gained.

Negative values represent Performance spent.

The UI explicitly explains that the application does not maintain a Performance total because the available source does not establish starting values, training-turn payments, or Lesson costs.

### Specification

`SCREEN_SPEC.md` updated for SCR-RUN-003.

### Validation

Invalid terminology such as:

```text
Mental
Vocal
Visual
Dancing
dance
```

is rejected.

Zero and decimal values are rejected.

Browser-form numeric strings are accepted.

---

## Slice 7 — TrainingDetail Consistency Boundary

**Status: COMPLETE**

Determine whether:

```text
resources/js/pages/Career/TrainingDetail.vue
```

is an equivalent committing turn-entry surface.

### Determination

It is **not**.

Although it submits to:

```text
runs.turns.store
```

it always submits:

```text
stage: preview
```

and does not reach the committing confirmation path.

Its own screen explicitly states that the turn is not written until confirmation occurs elsewhere.

### Decision

Do **not** add Performance controls to TrainingDetail.

Performance remains on the actual committing Runs/Show turn-entry surface.

### Regression protection

Added:

```text
tests/Feature/TrainingDetailPerformanceBoundaryTest.php
```

The tests establish that:

- TrainingDetail remains preview-only;
- no turn/event is written by its preview submission;
- Performance data can survive the preview hand-off;
- invalid Performance values are not silently dropped.

### Specification

`SCREEN_SPEC.md` SCR-CAR-012 updated with the boundary decision and reopen condition.

---

# Phase B — Lessons

## Slice 8 — Lessons Evidence + Foundation

**Status: COMPLETE** (ran 2026-10-09 and was re-tested the same day; the gate closed with no Lesson representation
established, recorded in `SCREEN_SPEC.md` §7-19)

Establish exactly what the project can truthfully represent about Lessons.

### First determine

- whether a canonical Lesson representation already exists;
- whether Lessons are observations, actions, or both;
- whether a Lesson has an established TurnEvent representation;
- whether Lesson categories/types are authoritative;
- whether costs are authoritative;
- whether Performance effects are authoritative;
- whether Hype/stat effects are authoritative;
- whether confirmation/reservation terminology is verified.

### Important boundary

The Grand Concert documentation explicitly identifies some Lesson-related information as unverified.

Do **not** implement:

- unverified Lesson labels;
- cost curves;
- per-level magnitudes;
- inferred Performance gains;
- inferred Hype;
- inferred stat effects.

### Possible outcome

If only a minimal Lesson observation is supported, implement only that foundation.

If no truthful representation is currently supported, document the boundary and add a regression test rather than inventing a Lesson system.

---

## Slice 9 — Lessons Turn Lifecycle

**Status: PLANNED**

Only if Slice 8 establishes a valid Lesson representation.

Integrate Lessons into the canonical turn/event lifecycle.

### Preferred architecture

Reuse:

- existing TurnEvent infrastructure;
- canonical turn write path;
- `TrainingRun` read-side conventions;
- existing scenario capability/configuration.

### Do not create

- Lesson table;
- standalone Lesson model;
- separate Lesson endpoint;
- duplicate turn-writing path;
- speculative calculation engine.

### Tests

Verify:

- valid Lesson representation;
- invalid values;
- persistence;
- readback;
- deterministic ordering;
- no accidental total/balance calculation.

---

## Slice 10 — Lessons Trainer UI

**Status: PLANNED**

Only if authoritative evidence supports a truthful Trainer-facing Lesson interaction.

### Preferred surface

The actual committing turn-entry surface.

Do not add Lesson controls to `TrainingDetail.vue`.

### UI constraints

- exact verified terminology;
- no invented defaults;
- no unverified costs;
- no unverified magnitudes;
- no automatic Lesson selection;
- no hidden calculations.

If evidence does not support truthful UI, document the exclusion instead.

---

# Phase C — Songs

## Slice 11 — Songs Evidence + Foundation

**Status: BLOCKED BY EVIDENCE** (gate run 2026-10-09; the determination is in `SCREEN_SPEC.md` §7-21)

Establish the authoritative Song representation.

### Outcome of the gate

**Songs — BLOCKED BY EVIDENCE.** The noun and the count are settled; nothing a field could carry is.

- **Settled:** **songs** is Cygames' own English (notice 905), so it needs no capture. The **23** and its
  availability segments are corroborated row for row by both publishers, and reference §7 row 49 treats the count
  and the segment gating as settled while leaving the naming open.
- **Not settled:** the language of the 23 titles (row 49, unresolved); the Performance type each cost figure
  belongs to (not recoverable from either page's text layer); any magnitude (no cost curve, no Hype per Song);
  whether acquiring a Song consumes a turn and what the client calls the action.
- **New measured negative:** no Song title appears in any committed data this repository holds. Of nine distinctive
  fragments from `docs/scenarios/07`'s title table grepped against `database/seeders/data/`, eight return 0 hits and
  the ninth, `Umadol`, matches only unrelated client skill and card names (`Rising to Umadol Stardom!`). The one
  local scenario JSON in the tree, `.scratch-uma/static_scenarios.json`, is a gitignored GameTora scratch copy that
  no commit has ever touched and it carries no Song key. Row 49 is therefore not closable locally; it needs the
  `[Global]` Lesson-menu frame that gap §7-19 already names.
- **Smallest truthful representation:** already exists and is generic. `TurnEventType::Scenario` with
  `source_name` and nullable `deltas` carries "the Trainer says this happened" for any scenario event, so a Song
  needs no enum, table, column, route, flag or component. A `Song` enum would pick a language the client was never
  read stating; a Song count would state a timing no source publishes.
- **Absence is guarded by committed tests:** `tests/Feature/GrandConcertPanelTest.php:93` (payload) and
  `tests/browser/grand-concert-panel.spec.ts:98` (markup) both refuse a `song` string.
- **Reopen condition:** a `[Global]` capture of the Lesson menu. Nothing before that is implementable without
  inventing client text or a number.

### Determine

- verified Song terminology;
- whether individual Song names are authoritative;
- whether Song acquisition is an observed event or action;
- whether Song spending/selection is represented;
- whether costs are verified;
- whether Song effects are verified;
- whether Hype effects are verified;
- whether Song timing is verified.

### Explicit warning

The existing Grand Concert documentation identifies the **23 Song titles** and other Song-related details as unverified where applicable.

Do not render or encode unverified Song data merely because it appears in an external source.

---

## Slice 12 — Songs Lifecycle Integration

**Status: BLOCKED BY EVIDENCE** (gated on Slice 11, which closed the gate: no truthful Song representation exists to integrate)

If Slice 11 establishes a truthful representation:

- integrate Songs into the canonical turn/event lifecycle;
- provide a typed read path where justified;
- preserve observed-vs-derived separation;
- avoid calculating unsupported Song state.

No new standalone subsystem unless existing architecture genuinely requires one.

---

## Slice 13 — Songs Trainer UI

**Status: BLOCKED BY EVIDENCE** (gated on Slice 11; there is no verified Song interaction or status to display)

Add the minimum verified Song interaction/status information to the appropriate committing surface.

Do not expose:

- unverified Song costs;
- unverified effects;
- unsupported Hype calculations;
- speculative Song availability;
- automatic recommendations masquerading as game facts.

---

# Phase D — Live Systems

## Slice 14 — Live Bonus Evidence + Foundation

**Status: BLOCKED BY EVIDENCE** (gate run 2026-10-09; the determination is in `SCREEN_SPEC.md` §7-22)

Establish what Live Bonus state can actually be represented.

### Outcome of the gate

**Live Bonuses — BLOCKED BY EVIDENCE.** The structure is the best-corroborated thing in this scenario and the
vocabulary is the worst-evidenced, and a field needs both.

- **Settled (tier A and B, not the client):** a Lesson pays two layers of reward and one is deferred. GameTora
  names both layers and their behaviour; Game8 independently describes the deferred activation ("Takes effect
  after concerts") while collapsing both under one heading, "Mastery Bonus". Two publishers that disagree on every
  noun agree on the shape. Reference §7 row 51 adopts the partition "for the mechanics" and adopts "neither name
  … for display".
- **Not settled:** every English word. The corpus carries **six renderings for three objects** — GameTora's
  Friendship Bonus / Speciality Rate Up / Support Event Chance Up against Game8's Friendship Training
  Effectiveness / Specialty Priority / Support Chain Event Frequency — and notice 905 "names neither bonus layer",
  which is what keeps row 51 open. Its "boosting training effects" sentence is about what a Lesson pays in
  general; reading it onto the deferred layer is inference.
- **Two measured negatives, both local.** In the app's own committed dictionary
  `database/seeders/data/support_effects.ca447e53.json` (all 35 rows read), `友情ボーナス` is id 1 with one English
  name, `得意率アップ` is id 19 with **two conflicting ones** (`name_en` "Specialty Priority", `name_en_eon`
  "Specialty Rate Up"), and `サポート連続イベント率アップ` has **no row at all** — so the divergence between the two
  guides is reproduced inside one file, and the third type of a three-type mechanic has no stored row. And
  "Friendship Bonus" is **already displayed copy** in this application, as a support-card effect name from that
  GameTora dictionary (`SupportEffect.php:17`, asserted by `SupportCardPageTest.php:85,93,253,261`), which is a
  trap rather than a settlement: different domain, no capture, and no source says the scenario's bonus and a
  card's effect are one counter.
- **The magnitude boundary is narrower than the shorthand in this slice said.** "No Live Bonus percentage ladder"
  is true, and "no number exists" is false: the **first-instance** value of each type is printed row for row by
  both publishers inside the Song table (`Friendship Bonus +5%` and `+10%`, `Speciality Rate Up +5`, `Support
  Event Chance Up +1`). What is absent is the level progression. Every one of those figures is third-party prose
  riding on a Song title whose language row 49 has not settled, so the standard `SCREEN_SPEC.md` §7-21 applied to
  Songs rejects them here too.
- **Single-source only, with no corroboration at all:** that a bonus persists for the entirety of the run, that it
  has levels raised by repeating the same bonus in another Lesson, and that automatically granted Songs do or do
  not pay one. GameTora's "Automatically gained songs also count" sentence is about the Hype gauge, not about
  bonuses.
- **Smallest truthful representation:** already exists, generic, and unchanged from Slices 8 and 11.
  `TurnEventType::Scenario` with `source_name` and nullable `deltas` records "I queued a Friendship Bonus this
  turn" today. A `LiveBonusType` enum would pick among six renderings the client was never read stating and would
  have to name a third case no committed row supports, so it fails the test `PerformanceType` passes. Percentage,
  level, duration and stacking fields, a current-bonus reader, and any Live Bonus → Song / Lesson /
  `support_effects` key are all unimplemented because unevidenced, not deferred.
- **Absence is guarded:** `tests/Feature/GrandConcertPanelTest.php:103`'s refusal list grew from five words to eight
  on this gate and now refuses `live bonus`, `practice bonus` and `mastery bonus` beside `song`, `lesson` and
  `hype`. No second structural file was added: `LessonEvidenceBoundaryTest.php:84` already pins that an
  unrecognised key on the turn write is collected as nothing and stored as nothing, and its structural refusals
  cover the same shape.
- **Reopen condition:** the same `[Global]` Lesson-menu capture gaps §7-19 and §7-21 name, and it owes this gate
  the layer label, each of the three type names as printed, and which type each Song row carries. One cheaper
  local route to try first, which does **not** close the gate alone: `docs/research-scratch/DESIGN-CORPUS.md:4261`
  records **Our Grand Concert: None found**, and the corpus's screen-type table (`:4269-4282`) documents no
  support-card effect list, only rail avatars, a Career Profile "Support Cards" row and an unread **Perks**
  button. Support effects are server-wide rather than scenario-specific, so an existing frame may already
  measure the client's word for `友情ボーナス` and `得意率アップ`. That settles two of six renderings for the
  support-effect domain only; a ladder needs no frame and has no source.

### Determine

Superseded by the outcome above. The five questions this slice asked (terminology, observations versus derived
values, sources of existence, per-value authority, and whether a current total is representable) are each answered
in §7-22: terminology unmeasured, existence corroborated at tier B, no per-value authority, and no current total
derivable.

### Explicit boundary

The existing documentation marks the **Live Bonus percentage ladder** as unverified. Confirmed and sharpened: the
ladder is unpublished while the first-instance values are published by both guides as third-party prose. Neither
is encodable.

Do not invent or encode it.


---

## Slice 15 — Live Bonus Lifecycle / Read Model

**Status: BLOCKED BY EVIDENCE** (gated on Slice 14, which closed the gate: no verified Live Bonus vocabulary or value exists to persist or read)

If Slice 14 establishes valid data:

- persist verified Live Bonus observations;
- expose them through canonical read-side architecture;
- preserve provenance;
- do not derive unsupported percentages.

No speculative balance/meter system.

---

## Slice 16 — Promotional Lives Evidence + Foundation

**Status: BLOCKED BY EVIDENCE** (gate run 2026-10-09; the determination is in `SCREEN_SPEC.md` §7-23)

Establish the authoritative representation and lifecycle boundary for Promotional Lives.

### Naming correction first

The `[Global]` client calls these **Promo Concert**, not "Promotional Lives". Cygames' notice 905 prints "**Four
Promo Concerts and one Grand Concert** are held, making for a total of five concerts", and Game8 independently uses
"Promo Concerts" too. "Promotional Lives" is GameTora's rendering of the JP word, which makes this Phase's own
title guide vocabulary rather than client vocabulary — the one Grand Concert mechanic in the whole line whose noun
is already settled at tier S, and settled against the roadmap's name.

That is a naming fact, not an implementation licence, and this slice changes no code. `docs/UMAMUSUME_REFERENCE.md`
§2.9 ruled the same thing on the day of the primary read; the guards already carry the asymmetry
(`GrandConcertPanelTest.php:107` and `tests/browser/grand-concert-panel.spec.ts:98` refuse `promotional live`, and
nothing refuses `promo concert` because that word is licensed copy). **Whether Phase D is retitled "Promo
Concerts" is the owner's decision and is raised, not made.**

### Outcome of the gate

**Promo Concerts — BLOCKED BY EVIDENCE.** Unlike Slices 8, 11 and 14, the blocker is not vocabulary. It is a
calendar position, a lifecycle, and a table that can hold either.

- **Settled at tier S:** the noun (**Promo Concert**, **Grand Concert**), the count (**four plus one, five
  concerts**), the cadence (**biannually, from the second half of December of the trainee's junior year**), the
  gauge's official name (**Live Performance Expectations**) and the max-gauge outcome (**Great Success**). No
  other Grand Concert mechanic has ever had this.
- **Not settled, and it is structural, not textual.** Neither timeline table can hold the series.
  `scenario_slots` has **no `year` column** and its live unique index is `(scenario_key, month, half, kind,
  source_key)`, so two Late-December concerts in different career years collide; `ScenarioSlot::VALID_KINDS` is
  `goal_race / team_race / grade_deadline / scripted_event / free_race` with no concert kind, and the model's
  `saving` guard throws on any other. `race_catalog_slots` does carry `year`, but it is race-shaped (non-null
  `grade_code`, plus `distance`, `surface`, `track_id`) and engine-owned from the GameTora instance export.
- **Measured negative:** searching every committed race name for Concert, Live, Promo or Grand returns **zero
  rows**. The catalog holds the scenario's finale (`final_live => our_grand_concert`, mandatory, the value KI-71
  corrected at `480b711`) and none of its four Promo Concerts — so the finale is a calendar row and the concerts
  are nothing.
- **Timing is not derivable without invention.** No source prints the (year, half) positions. GameTora's "before
  the 1st/2nd/3rd/4th Promo Live" segments gate **Song lesson unlocks**, not concert dates; Game8's per-year
  figures are the same unlock pattern. Placing the first concert needs this app's mapping of "junior year" to a
  career-year integer, which no source supplies.
- **Lifecycle unknown in every direction the app would need:** whether a concert occupies a turn (`docs/scenarios/07`
  states the turn shape of the live events was never published as a turn list), whether it can be declined or
  missed, and whether a confirmation, stage or result screen exists. GameTora's "you may choose to do lessons
  before stepping onto the stage … like you would learn skills before races" is an analogy for the *lesson* step,
  not evidence of a concert screen.
- **The outcome set is one word deep.** **Great Success** is official; `大成功` is the JP string; GameTora's "great
  or normal success" is its own English. No source names the other outcomes as printed or says how many tiers
  exist, so an outcome enum ships one licensed case and one inferred case.
- **Costs:** no source states a concert consumes Performance, and the one official relation runs the other way —
  gauge max *guarantees* a Great Success. Its subject is the still-unbuilt Live Performance Expectations concept,
  so the relation stands recorded and unmodelled.
- **Every payout is single-source:** 5 skill points per Technique Lesson and 25 per Song Lesson, and the +50
  resource-cap rise (GameTora); "each Promo Concert grants a stat increase" (Game8); "a Great Success raises stat
  caps" (GameTora); "the outcome of the Promo Lives feeds the finale" (GameTora, no mechanism named). The notice
  "publishes no number of any kind".
- **Smallest truthful representation:** already exists, generic. `TurnEventType::Scenario` with `source_name` and
  nullable `deltas` records "the second Promo Concert happened at turn N and was a Great Success" today, in words
  the Trainer supplies. The fourth consecutive gate to reach that finding.
- **Rejected as invention:** `PromotionalLiveType`, `PromotionalLivePayload`, a model or table, a state machine, a
  milestone scheduler, a cost or reward calculator, a `concert` kind in `scenario_slots` (that is a schema change,
  which §11 makes travel with a migration, an `ARCHITECTURE-ESSENTIALS.md` digest, a PRD citation and an ADR —
  spent backwards to hold a schedule no source pins down), synthetic `race_catalog_slots` rows for a non-race, and
  any Promo Concert → Song / Lesson / Live Bonus / Hype key.
- **Stale claim reported, not edited:** `database/migrations/2026_09_27_153416_create_scenario_slots_table.php:15`
  still says "Our Grand Concert has no guide at all", false since the 2026-10-05 read. Per `AGENTS.md` §2 the code
  wins and the rule is stale; a schema file's prose is not this slice's surface.
- **Reopen condition:** **two** captures, and one alone is not enough — a `[Global]` **pre-live / backstage screen**
  (the event's own name, the gauge label, whatever the client calls the action) **and** a `[Global]` **career
  calendar or goal row** showing where the four concerts sit, because only a screen settles a (year, half)
  position. Magnitudes need a source rather than a frame: no screen resolves 5 / 25 skill points or the +50.

### Determine

Superseded by the outcome above. The five questions this slice asked are each answered in §7-23: what constitutes
it (a scheduled concert, official, but with no unplaceable-in-data date), what can be observed (the event and a
licensed outcome word), what can be entered (nothing the app may prefill), which outcomes are authoritative (one),
and what remains unverified (turn shape, interaction, costs, payouts, repeatability, the finale relation).

Do not assume mechanics from external/community sources.

---

## Slice 17 — Promotional Lives Lifecycle

**Status: BLOCKED BY EVIDENCE** (gated on Slice 16, which closed the gate: no Promotional Live representation exists and no table can hold the series without a schema change)

Integrate verified Promotional Live observations/results into the existing run lifecycle.

Reuse existing:

- TurnEvents;
- run state;
- read-side methods;
- capability configuration.

No duplicate subsystem.

---

## Slice 18 — Promotional Lives Trainer UI

**Status: BLOCKED BY EVIDENCE** (gated on Slice 16; there is no verified interaction, schedule or status to display, though the event's own noun is official)

Add the minimum truthful Trainer-facing Promotional Live interaction/status surface.

Do not expose speculative success formulas, Hype effects, bonus percentages, or rewards.

---

# Phase E — Grand Concert + Intelligence

## Slice 19 — Grand Concert Finale State Foundation

**Status: COMPLETE** (landed 2026-10-09 at `aa5d975`; the reasoning is in
`app/Services/Scenario/FinaleReader.php`'s docblock and `CockpitController::finaleAbsence()`, and the
cases are in `tests/Feature/FinaleAbsenceTest.php`)

What the slice asked for is settled by reading the catalogue rather than the config. The finale is a
scenario-scoped mandatory row in the career calendar, so `CockpitController::finaleAbsence()` answers
from that row instead of from the `config/scenarios.php` `finale` key, which stays undeclared because
no component reads its contents. Three answers, and the branch is driven by the row's presence rather
than by a scenario name, so a scenario name in the payload path would be a second source for the
layout path, which `AGENTS.md` §7 forbids.

- On the calendar: the absence sentence names the mandatory race and states that the scenario declares
  no structure beyond it.
- Not on the calendar: the sentence denies a finale, which is now true rather than a blanket claim.
- Declared structure: no absence sentence at all, because there is nothing absent.
- A row belonging to another scenario is ignored, so a shared catalogue cannot leak one scenario's
  finale into another's screen.

No finale state machine was built. The representation is the catalogue row's presence plus the run's
own reading of it, which is what Slice 20 reports.

---

## Slice 20 — Grand Concert Execution + Reporting

**Status: COMPLETE** (landed 2026-10-09 at `aa5d975`; `SCR-CAR-018`, cases in
`tests/Feature/FinaleReportingTest.php`)

The verified finale observations reach the existing read-only reporting path through `ResultController`
and `/run/{run}/result`, with no new endpoint and no speculative success calculation. One server-side
reader (`FinaleReader`) feeds both the Career Result and the Cockpit, so no two screens can disagree.

Three states, and the middle one is the one nothing could previously say:

- **Not yet reached.** `placement` and `turn` are null rather than zero or an empty string, because a
  screen that printed an empty placement cell for a finale nobody has run would state a finish that
  does not exist (D-220).
- **Reached with nothing recorded.** The position is read from the highest logged turn against the
  finale block, so one row is enough; no count is taken.
- **Recorded.** The run's own stored outcome word, placement and turn. The outcome set is one word
  deep: the run's status label, not a finale-specific vocabulary.

A scenario whose calendar carries no finale row emits no finale state at all, and the old `finale` key
is gone rather than left standing as a null, which is how a reader tells a scenario with no finale row
from a payload that never carried the key. The catalogue row's own title is not carried: it reads
"URA Finals Final (Grand Live)" and is filed as KI-82.

---

## Slice 21 — Scenario-Aware Trainer Advisor

**Status: COMPLETE** (landed 2026-10-09 at `ad8dc77`; `ScenarioAdvisorFinaleTest`, cases in tests/Feature/ScenarioAdvisorFinaleTest.php)

Only after underlying Grand Concert state exists.

Update Trainer Advisor so that it can consume verified Grand Concert-specific state.

### Critical rule

Do not make Advisor scenario-aware by inventing scenario mechanics.

Advisor recommendations must distinguish:

```text
Observed fact
Derived fact
Recommendation
```

and maintain provenance.

---

## Slice 22 — Scenario Cockpit Integration

**Status: COMPLETE** (landed 2026-10-09 at `aa5d975`; `CockpitFinaleStripTest` and `CareerCockpitTest` pin the scenario-state surfaces, cases in tests/Feature/CockpitFinaleStripTest.php)

Expose verified Grand Concert state through the existing Career Cockpit/turn flow.

Respect existing:

```text
config/scenarios.php
```

capabilities and panel gates.

Do not turn every scenario mechanic into a new panel.

Use existing cockpit composition patterns.

---

# Phase F — Acceptance / Hardening

## Slice 23 — Cross-Feature Consistency Audit

**Status: COMPLETE** (`aa5d975`; see the Cross-Feature Consistency entry in the Current Status table, page 1125)

Audit the complete chain:

```text
Performance
    ↓
Lessons
    ↓
Songs
    ↓
Live Bonuses
    ↓
Promotional Lives
    ↓
Grand Concert
```

Verify that each feature:

- uses canonical architecture;
- has no duplicate state representation;
- preserves observed-vs-derived separation;
- has consistent TurnEvent behavior;
- has deterministic read-side behavior;
- has correct scenario capability gating;
- does not expose unverified mechanics;
- does not silently calculate unsupported totals;
- has appropriate tests;
- has matching SCREEN_SPEC documentation.

Only fix demonstrated inconsistencies.

No broad refactor for aesthetic reasons.

---

## Slice 24 — Full Grand Concert Acceptance Pass

**Status: COMPLETE** (see the acceptance record at 2026-10-09, below)

Perform the final scenario readiness assessment.

### Verify

#### Scenario entry

- scenario registration;
- scenario selection;
- trainee selection;
- training target;
- inheritance;
- support deck;
- preflight.

#### Career lifecycle

- run creation;
- turn entry;
- Performance;
- Lessons;
- Songs;
- Live Bonuses;
- Promotional Lives;
- finale;
- Career Report;
- veteran/Legacy handoff.

#### UI

- capability visibility;
- panel visibility;
- turn-entry surfaces;
- validation;
- confirmation;
- readback;
- empty states;
- provenance/explanations.

#### Data integrity

- scenario-scoped race catalog;
- no duplicate finale;
- no speculative database state;
- no accidental canonical DB mutation.

#### Tests

Run the appropriate project testing levels according to `AGENTS.md §9`.

Only claim gates that were actually executed.

### Acceptance record — 2026-10-09

The scenario is complete as far as current evidence permits. Every actionable criterion above is
verified against the passing feature tests on this tree, not against a remembered state:

- **Scenario entry** — `CareerScenarioSelectTest`, `CareerTraineeSelectTest`, `CareerBuildTargetTest`,
  `CareerInheritanceEventTest`, `CareerLegacyDeckStepsTest`, `CareerPreflightTest`: green.
- **Career lifecycle** — `CareerCockpitTest` (run creation, turn entry, correction, status/scenario/period
  writes), `CareerTrainingDetailTest` (Performance), `CareerResultTest` (Career Report),
  `CareerSaveVeteranTest` (veteran handoff): green.
- **UI** — `GrandConcertPanelTest` and `ScenarioPanelTest` (capability visibility, panel visibility,
  validation, readback, empty states, provenance): green.
- **Data integrity** — scenario-scoped race catalog and the finale reads are pinned by
  `CockpitFinaleStripTest`, `FinaleAbsenceTest`, `FinaleReportingTest`, `RunRaceStripTest`: green.
- **Blocked mechanics (Lessons, Songs, Live Bonuses, Promo Concerts)** — the acceptance is the absence,
  and it is stated: `config/scenarios.php:480`'s `panel_absence` ("The mechanics for this scenario are
  sourced, but no capture of the client's own screens exists yet, so no panel is drawn."), asserted by
  `GrandConcertPanelTest`. No surface invents a mechanic.

**Gates, as actually run.** `php artisan test --compact`: **8 failed, 2 skipped, 1489 passed (26155
assertions), 341.74s**. All eight failures are the F2 cutover's, not this scenario's: six in
`StatBandTest` (its `StatBand`/`GradeBadge` subject retired with `Runs/Show.vue`, the F2 close-out's
Delta C) and two in `TurnEntryFieldSourceTest` (stale assertions left by `1ec92ab`, the energy-state
change). `npm run typecheck` exit 0; `npm run build` exit 0; `composer lore` exit 0 (302 pre-existing
hits); `composer lore-code` exit 0 (118 pre-existing hits).

**Browser walk: not completed.** The harness cannot own its port: a leaked `php artisan serve` holds the
default `:8127` (KI-73), and the box was contended enough that the suite ran ~46s per case (KI-74), so
the run was voided as a signal rather than read. The failures seen before the void were all F2 cutover
leftovers — `career-cockpit.spec.ts`'s stale "run screen" link copy, `career-result.spec.ts`'s stale
`runs.show` status flow, and `run-detail.spec.ts` driving the redirected page — and the concurrent
session was repairing them in the working tree during this pass (`career-cockpit.spec.ts:114` and
`career-result.spec.ts:35` are already repointed, and `run-detail.spec.ts` is staged for deletion).

*Corrected 2026-10-10.* The sentence above that these eight failures were "repaired by a concurrent session during the pass" is superseded. Of the eight, **six were in `StatBandTest`** and were **retired** under the owner's ruling R-A1 (the subject, `GradeBadge.vue` and `StatBand.vue`, was deleted by F2's Delta C cleanup). The remaining **two were in `TurnEntryFieldSourceTest`** and were **adapted by F2** in a later commit after `1ec92ab` changed `TrainingDetail.vue`'s import. All eight were F2's to close; F2 closed them outside the Grand Concert line. The Grand Concert acceptance did not see them repaired mid-run.

---

# Global Out-of-Scope Rules

These rules apply to every remaining slice unless a future slice explicitly establishes verified evidence for them.

Do not:

- invent game mechanics;
- invent numeric costs;
- invent starting values;
- invent caps;
- invent Hype formulas;
- invent Live Bonus percentages;
- invent Song effects;
- invent Lesson effects;
- infer unsupported Performance conversions;
- create speculative recommendation logic;
- create duplicate models/tables;
- create duplicate endpoints;
- create a second turn-writing path;
- add unnecessary global state;
- introduce a new frontend framework;
- perform broad refactors;
- redesign unrelated screens;
- modify unrelated concurrent work.

---

# Repository Safety Constraints

The following areas are protected and must not be modified by scenario slices unless explicitly authorized by a separate task:

```text
config/database-safety.php
app/Services/DatabaseSafety/
tests/browser/global-setup.ts
playwright.config.ts
.scratch-uma/incident-2026-10-08-devdb/
.scratch-uma/db-recovery-2026-10-08/
composer.json
composer.lock
D:/Projects/uma_musume_race_planner/
```

Never run destructive database commands against the canonical database.

Do not use the canonical development database as a disposable test database.

Do not commit changes unless a separate instruction explicitly authorizes a commit.

Do not clean or revert unrelated concurrent work.

---

# Slice Completion Contract

Every slice must end with a report containing:

1. **Determination**
2. **Evidence**
3. **Files changed**
4. **Architecture decision**
5. **UI behavior**
6. **Mechanics explicitly not implemented**
7. **Tests and gates actually run**
8. **Safety/concurrency confirmation**
9. **Git/commit status**
10. **Explicit stop**

An agent must stop after its assigned slice.

A later slice may be started only after the previous slice has been reviewed/accepted.

---

# Current Status

```text
Phase A — Performance
  Slice 1  Baseline audit                         COMPLETE
  Slice 2  Finale race catalog correction        COMPLETE
  Slice 3  Performance foundation                 COMPLETE
  Slice 4  Performance turn-write                COMPLETE
  Slice 5  Performance read-side                 COMPLETE
  Slice 6  Performance Trainer input              COMPLETE
  Slice 7  TrainingDetail boundary                COMPLETE

Phase B — Lessons
  Slice 8  Evidence + foundation                  COMPLETE (gate closed: no Lesson representation exists)
  Slice 9  Turn lifecycle                         BLOCKED BY EVIDENCE
  Slice 10 Trainer UI                             BLOCKED BY EVIDENCE

Phase C — Songs
  Slice 11 Evidence + foundation                  BLOCKED BY EVIDENCE
  Slice 12 Lifecycle integration                  BLOCKED BY EVIDENCE
  Slice 13 Trainer UI                             BLOCKED BY EVIDENCE

Phase D — Live systems
  Slice 14 Live Bonus foundation                  BLOCKED BY EVIDENCE
  Slice 15 Live Bonus lifecycle                   BLOCKED BY EVIDENCE
  Slice 16 Promotional Lives foundation          BLOCKED BY EVIDENCE (client noun is Promo Concert)
  Slice 17 Promotional Lives lifecycle            BLOCKED BY EVIDENCE
  Slice 18 Promotional Lives UI                  BLOCKED BY EVIDENCE

Phase E — Grand Concert + Intelligence
  Slice 19 Finale state foundation                COMPLETE
  Slice 20 Finale execution/reporting             COMPLETE
  Slice 21 Scenario-aware Advisor                 COMPLETE
  Slice 22 Scenario cockpit integration           COMPLETE

Phase F — Acceptance
  Slice 23 Cross-feature consistency audit        COMPLETE (aa5d975)
  Slice 24 Full Grand Concert acceptance          COMPLETE (see the acceptance record, 2026-10-09)
```

## Definition of "Complete"

Our Grand Concert is considered implementation-complete only when the existing Trainer Desk architecture can truthfully represent and expose the verified scenario lifecycle from career setup through Grand Concert completion, while clearly distinguishing:

```text
Observed Game State
        ↓
Derived State
        ↓
Recommendation
```

and without presenting unsupported game mechanics as facts.

**Completion does not mean implementing every mechanic found on external wikis.**

It means implementing everything the project's authoritative evidence supports, documenting what remains unknown, and ensuring the application never fabricates certainty where the underlying game data has not been verified.