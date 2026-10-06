# ADR-0015: A stat's ceiling is its scenario's per-stat cap

Status: **Accepted (owner decision 5, 2026-09-30).** Supersedes the validation bound of `ADR-0002`
option B and implements the intent of `ADR-0003` decision 6's amendment. Does not edit either file.

## Decision

A turn entry's stat ceiling is **that scenario's own per-stat cap**: `base_cap` plus the scenario's
`cap_bonus` for that stat, clamped to the engine `hard_cap`. `StoreTurnEntryRequest` reads it from
`App\Services\ScenarioCaps`, which is now the single owner of the arithmetic that `x-stat-band`
previously computed inline. The two call sites cannot disagree because only one of them does the
sum.

| Scenario            | Speed   | Stamina   | Power   | Guts   | Wit    |
| ------------------- | ------- | --------- | ------- | ------ | ------ |
| URA Finale          | 1400    | 1400      | 1400    | 1400   | 1400   |
| Unity Cup           | 1300    | 1300      | 1300    | 1300   | 1800   |
| Trackblazer         | 1200    | 1900      | 1200    | 1200   | 1500   |
| Our Grand Concert   | 1600    | 1300      | 1300    | 1500   | 1300   |

Derived from `config/scenarios.php` (`base_cap` 1200, `hard_cap` 2000, per-scenario `cap_bonus`),
whose header dates that table to 2026-09-27 against three Global sources. The table above is the
consequence, not a second source: if the matrix changes, these numbers and the tests move together.

**A run with no scenario is held to `base_cap`, with no bonus.** The five stats validate at 0..1200.

**Skill points get no ceiling.** `sp` stays `nullable|integer|min:0`.

## Why this is the shape, not flat 2000

`ADR-0002` recorded two options and chose B as a stopgap:

> Option A, the per-scenario reference table, remains the better long-run shape and is not cancelled;
> the owner chose B for the bound specifically so that data entry stops failing while A is still
> unfunded.

This is option A, funded. Reading the scenario's own number was always the intent — `ADR-0003`
decision 6's amendment says "**2000 is a value, not the rule**" and that the bound "must therefore be
read from the scenario's own stored `scenarios.hard_cap`, with 2000 as the value that happens to apply
to all four Global scenarios today."

The amendment named `hard_cap`, and this decision reads the **per-stat cap** instead. That is
deliberate, and the reason is the parser's own comment: `GametoraScenarioParser.php:16` describes
`hard_caps` as "the database ceiling **above** the in-run cap". The two numbers answer different
questions. `hard_cap` bounds what the database will store; `base_cap + cap_bonus` bounds what a
Trainer can actually reach in that scenario. Validating at `hard_cap` would accept Speed 2000 in URA
Finale, whose in-run ceiling is 1400 — and `x-stat-band` would draw a bar end at 1400 around a number
the form had just accepted. That is the comprehension defect `ADR-0002`'s UI condition exists to
prevent, re-imported through the back door. So `hard_cap` is kept where it belongs: the clamp, not
the target.

`ADR-0002`'s UI condition is untouched. The 1,200 halved-gains line and the scenario ceiling remain
two separately-drawn markers (`DESIGN.md` §6.5); the band still computes `$softPct` and `$atCeiling`
from `base_cap` independently of the bar end, and this change moves only the bar end's number.

## Why no bonus for a run that chose no scenario

`scenarioKey()` resolves null to the baseline so the resource strip has a descriptor to compose from,
and that resolution is an owner ruling (2026-09-27), recorded in `TrainingRun`'s own docblock. It is a
decision about *needing a shape to render*, not a claim that the run is playing that scenario.

A bonus is a property of a scenario the Trainer chose. Granting URA Finale's +200 to a run that never
picked it would accept numbers justified by a fact nobody entered. The tool already refuses exactly
this on the panels: `runs/show.blade.php` gates the whole panel block on `hasScenario()` because
"borrowing the baseline's schedule would tell a Trainer their race calendar is Oka Sho when they have
not picked a scenario at all" (D-220, D-221). Validation is the same case with a number instead of a
race name.

## Skill points stay uncapped

No source in the corpus states an upper bound on SP. `9b774f9` removed the previous `between:0,1200`
without recording why, which left an undocumented gap rather than a decision — the ceiling it deleted
was inherited from the stat bound it did not belong to. Restoring a number now would be inventing one.

So the decision is to keep `min:0` and name the absence, which converts an unexplained deletion into
a recorded position. The consequence is stated plainly: a mistyped `99999` in SP is accepted, and
nothing in the tool will catch it. If the owner later wants a guard, it should arrive as a UX limit
with its own source, not as a recycled stat cap — SP is not one of the five rated stats and the
1,200 halving rule does not apply to it.

## Deviation from the prior ruling, and its cost

`ADR-0003` decision 6 said to read the scenario's **stored** `hard_cap` column. This reads
`config('scenarios.hard_cap')` for the clamp instead.

The reason is dependency direction: `scenarios.hard_cap` is populated by the fetch engine, so a
validation path that read it would refuse or accept turns based on whether a fetch had ever run on
that machine. A local-only tool with an un-fetched database would get a nonsense bound. Config is
committed, deterministic, and is already what the band renders from.

The cost is that one number now has two homes: `config/scenarios.php` and the `scenarios` table's
`cap_speed..cap_wit` and `hard_cap` columns, the latter written by `GametoraScenarioParser`. Nothing
checks that they agree. That is recorded as an open finding in the consolidation, not resolved here;
reconciling them means deciding which one is authoritative, and the fetch engine's own errata history
makes that the owner's call rather than this slice's.

## Consequences

- The bound a Trainer sees as a bar end is the bound the form enforces. One owner, `ScenarioCaps`.
- URA/Unity Cup/Trackblazer/OGC entries can record their real ceilings; the 2026-07-01 rework's
  higher caps no longer bounce off a 1200 wall.
- A no-scenario run is stricter than it was under `ADR-0002` B (1200 rather than 2000). It was already
  1200 before B, so this restores the prior behavior rather than tightening a shipped one.
- `tests/Feature/ScenarioStatCapsTest.php` pins per-scenario and per-stat acceptance, the one-past
  rejection, the no-scenario rule, and the clamp. Two existing tests keep their assertions and change
  their stated reason: `TrainingRunTest` now names the base cap, and `GuidedTurnValidationTest` names
  the scenario ceiling of 1400 instead of "the 1200 cap".

## Erratum, 2026-10-01: the first consequence did not hold

> - The bound a Trainer sees as a bar end is the bound the form enforces. One owner, `ScenarioCaps`.

**That sentence was written on 2026-09-30 and it was false on the no-scenario branch until `04658f2` landed
on 2026-10-01. It is kept verbatim because it states the intent, and the intent was not achieved by the
mechanism chosen to achieve it.**

`ADR-0015` moved both readers onto `App\Services\ScenarioCaps`, and that half worked: the arithmetic has one
owner and the inline `base + bonus` sum is gone from the view. What it did not unify was **the argument each
reader passes in**. The validator calls `forRun($run)`, which refuses a bonus to a run that named no scenario.
`TrainingRunController::showData()` built the band payload from `$run->scenarioKey()`, which resolves null to
`config('scenarios.baseline')` — `ura_finale` — and the band then called `ScenarioCaps::stat()` on *that*. So a
no-scenario run displayed a bar ending at **1,400** while its own form carried `max="1200"`, and the one owner
supplied both numbers correctly. The disagreement survived inside `ScenarioCaps`, not beside it, which is why
reading the ADR was not enough to find it: the file describes a single owner, and there was one.

`x-stat-band`'s own `@props` comment had already named the hazard — a named default was removed because
"silently resolving to the baseline would rate a trainee against the wrong ceiling" — and the component honoured
that rule perfectly while the caller resolved the value one line earlier. A guard on the consumer does not
survive a producer that resolves before the boundary.

**The fix, and its shape.** The band no longer derives a ceiling at all. It takes a required `caps` map and a
`scenario` that is only the truth of the label and the footer's bonus breakdown; the caller passes
`ScenarioCaps::forRun($run)` and the run's real scenario or null. A run with no scenario now renders `/ 1,200`
on all five bars and states `1200 base, no scenario set: every ceiling here is the base cap and no bonus
applies`, and the footer for a chosen scenario keeps its `Speed +200, …` breakdown unchanged. The original
ruling anticipated a one-line call-site edit; it became a props-contract change for the reason above — passing
caps alone would have left the footer reading bonuses off a scenario key, which is the same coupling that
caused the bug. `docs/design-research/verification/slice-1-stat-ceilings-2026-09-30.md` §3 had already named
this defect and deferred it as the owner's call; that deferral is discharged here.

**The durable rule for the next single-owner refactor:** unifying the function is not unifying the call. Where
one number is read by a validator and a renderer, the record should say which *argument* both must pass, not
only which helper both must call. ADR-0015's consequence list is the place a future reader checks first, so the
qualification belongs here rather than only in the issue register.

Date: 2026-09-30
Relates to: `ADR-0002` (bound superseded here), `ADR-0003` decision 6 (intent implemented),
`ADR-0004` (aptitude and cap reference data), `PRD.md` US-3 / FR-C-2 / C-2, `CONSTRAINTS.md` D-31,
D-160..D-165, D-220, D-221, `DESIGN.md` §6.5
