# ADR-0002: Scenario-aware stat caps exceed the validation bound

Status: **accepted. Owner promoted US-10 to P1 and approved the schema expansion via `ADR-0003`.** **Amended twice on 2026-09-27**; the caps are now measured from game data rather than sourced from prose guides, which settles what to widen the bound to: `hard_caps` = **2000**, a field the game itself carries.

## Decision, 2026-09-27

**The owner ruled option B.** Validation widens to **0..2000**, sourced from the `hard_caps` field rather than an invented round number, and it is to land alongside the `ADR-0003` schema work.

The ruling came with a UI condition that is part of the decision, not a follow-on: **the 1,200 halved-gains line and the scenario ceiling must stay two visible, differently-drawn markers.** Raising the bound silently would trade a data defect for a comprehension defect, because 1,200 is the moment a run's training economics change and a Trainer has to be able to see they have crossed it. `DESIGN.md` §6.5 carries the rendering requirement and `screen-a-scenario-v10.html` implements it as a dashed tick plus a bar end plus an amber halved region.

Option A, the per-scenario reference table, remains the better long-run shape and is not cancelled; the owner chose B for the bound specifically so that data entry stops failing while A is still unfunded.
Date: 2026-09-27
Relates to: `ADR-0001` (Energy scope), `PRD.md` FR-C-2 / US-3, `CLAUDE.md` Planner Rule 6, `docs/design-research/CONSTRAINTS.md` D-31, D-160..D-165

## Problem

`PRD.md` FR-C-2 fixes `TurnEntry` stats at **0..1200**, and US-3 makes "a stat outside 0..1200 is rejected" an acceptance test. `CLAUDE.md` Planner Rule 6 places the bound in `StoreTurnEntryRequest`.

`UMAMUSUME_REFERENCE.md` §1.3.4 records that every live Global scenario caps higher. The table below is now restated from the game's own data — see the second amendment — with one row corrected:

| Scenario | Speed | Stamina | Power | Guts | Wit | Hard cap |
|---|---|---|---|---|---|---|
| URA Finale | 1400 | 1400 | 1400 | 1400 | 1400 | 2000 |
| Unity Cup | 1300 | 1300 | 1300 | 1300 | **1800** | 2000 |
| Trackblazer | 1200 | **1900** | 1200 | 1200 | 1500 | 2000 |
| Our Grand Concert | **1600** | 1300 | 1300 | 1500 | 1300 | 2000 |

**The tool cannot record a real run.** A Unity Cup Trainer reaching Wit 1350 is refused by a P0 feature, and so is a Trackblazer Trainer at Stamina 1350. This is not a display problem and no amount of UI work fixes it; the bound itself is wrong. It has been wrong for every Global player who crossed 1200 since the 2026-07-01 rework.

An earlier revision of `docs/design-research/CONSTRAINTS.md` D-31 instructed the UI to display `/1200` and never imply a wider ceiling. That instruction is withdrawn: it dressed a schema limitation as a design decision and would have made the defect look intentional.

## Why this is not simply "raise the number to 2000"

Three things block the obvious fix.

**1. The cap is derived, and two inputs do not exist in the schema.** §1.3.4 adds breakthrough, +16 per ★3 inherited basic-ability factor applied at three separate moments, and per-stat support-card 「限界値アップ」 effects. Support cards are cut in Phase 1 (PRD §6.9) and inheritance is two nullable FKs with no factor grades stored. Any single displayed ceiling is therefore incomplete, and presenting it as complete would violate Planner Rule 5.

**2. `scenario` is free text.** `ARCHITECTURE.md` defines `training_runs.scenario` as `string nullable`. A cap set cannot be resolved from an arbitrary string, and the four Global scenario names have no canonical form in the database. Scenario awareness requires an enum or a reference table.

**3. The cap data was not clean — this blocker is now cleared.** At the time of writing, all four primary cap sets traced to Game8 2025-11-21 and Kamigame 2024-02-19, both flagged `⚠️ STALE`, and two rows were single-source. That is resolved: the scenario table is available as structured data with `stats` and `hard_caps` fields, and it reproduces the four Global rows exactly. The provenance obligation is unchanged — if caps become stored facts they need source URL, fetched date and server qualifier per FR-A-4, and a fact without provenance is deleted rather than stored. What changed is that the provenance can now name a dataset field instead of a prose guide.

## Options

**A. Raise the bound to a per-scenario cap, add a `scenarios` reference table, and display the cap as a labelled stack with untracked components named.** Correct, and the only option that makes US-3 work. Cost: one migration, one enum or table, re-verification of six stale-sourced cap sets, and a provenance decision.

**B. Raise the bound to a flat 2000 and treat scenario caps as display-only.** Cheapest, unblocks data entry immediately. The tool accepts everything, and the per-stat bar scales to the scenario base for presentation. Loses the ability to warn a Trainer approaching their real ceiling, and 2000 is the export's recorded hard ceiling rather than a gameplay cap.

**C. Keep 1200 and label the tool Ura-Finale-only.** Rejected: contradicts the brief's requirement that the tool be scenario-aware, and still fails on Ura Finale above 1200.

**D. Defer to Phase 2.** Rejected: US-3 is P0. A P0 story that cannot accept real data is not a Phase 1 exit, and every mockup in this package depicts bars the app would refuse to save.

## Recommendation

**Option A**, with the cap stack rendered per `DESIGN.md` §6.21 and the disclosure line naming untracked components. If the owner wants Phase 1 to close fast, **Option B is the acceptable fallback**: it unblocks data entry and costs one validation constant, and the scenario reference table can arrive in Phase 2 without any UI change, because the bar already scales per-stat and only the denominator source moves.

Either way the bound change must land before implementation, because the current design documentation and the current schema cannot both be true.

## What this does not decide

Whether support-card and breakthrough contributions are ever tracked. That is PRD §6.9 territory and out of scope here; the design requirement is only that the UI says what it is not accounting for.

## Links

- `UMAMUSUME_REFERENCE.md` §1.3.4, §1.5, §1.6.1
- `PRD.md` FR-C-1, FR-C-2, FR-A-4, US-3, §6.9
- `CLAUDE.md` Planner Domain Rules 5, 6
- `ARCHITECTURE.md` §3 Trainer-data domain
- `docs/design-research/DESIGN.md` §6.21
- `docs/design-research/CONSTRAINTS.md` D-31, D-160..D-165, G-15a..c

## Amendment, 2026-09-27, after reading the URA Finale guide

The framing above treated 1200 as an arbitrary app constant standing in the way of the real caps. Reading `game8.co/.../archives/536520` in a browser corrected that, and it improves the fix rather than weakening it.

Two facts change the recommendation:

1. **"Training gains for stats beyond 1200 are always halved."** 1200 is a genuine in-game soft cap, and a named mechanic, `Fully Charged`, requires Power 1200 or more. So the number is meaningful and worth rendering, not a limit to hide.
2. **"The URA Finale Scenario now has +200 stat caps."** A ceiling is `1200 base + 200 scenario`, not a flat 1400. The cap is a sum, which is exactly the stack model `DESIGN.md` §6.22 already proposed.

**Revised recommendation.** Option A stands, but the bar renders **two distinct markers**: the 1200 halved-gains line, which is a game fact, and the scenario ceiling, which is a different game fact. Rejecting 1350 was never the only defect; the UI also had no way to show a Trainer that they had crossed into halved returns, which is the moment a run actually changes character.

## Amendment 2, 2026-09-27, after measuring the caps from game data

The first amendment still sourced caps from prose guides and left option B's 2000 as an approximation ("the export's recorded hard ceiling rather than a gameplay cap"). Both readings improved once the scenario table was pulled as JSON instead of read from guides.

**The data.** GameTora serves a `scenarios` dataset (manifest key `scenarios`, hash `61b7c51c` at fetch time, fetched 2026-09-27). Each row carries `stats` — a five-element array — and `hard_caps`. Two independent facts show `stats` is a **bonus over a 1200 base**, not a ceiling:

1. Every row reproduces a published cap exactly when added to 1200: URA `[200,200,200,200,200]` → 1400, matching Game8's "+200 stat caps"; Unity Cup `[100,100,100,100,600]` → 1300/1800, matching "+100 … +600 Wit"; Our Grand Concert `[400,100,100,300,100]` → 1600/1300/1300/1500/1300, matching §1.3.4.
2. JP scenario id=8 has a **negative** Stamina bonus of `-200`, i.e. a cap of **1000**, below the base. A field that can sit under the base cannot be a ceiling.

**Corrections this forced.** `UMAMUSUME_REFERENCE.md` §1.3.4's Climax row reads `…/1500/1200` for Guts/Wit; the data says `…/1200/1500`. **The row is transposed**, and this ADR's table and `DESIGN.md` §6.22 both inherited it under the Global name "Trackblazer". Stamina 1900 is correct in either ordering, which is why the error survived: the only number large enough to break validation was right. Separately, `docs/scenarios/05-trackblazer-gametora.md` predicted Global would keep flat 1200 caps; it was written on launch day and the 2026-07-01 rework overturned it.

**What this does to the options.** The bound question is no longer a judgement call about how round a number to pick. The game carries an explicit ceiling field, `hard_caps = 2000` for all four Global scenarios, so:

- **Option B is upgraded, not merely tolerated.** Validate `0..2000` against a named, sourced field rather than an invented one. The objection that 2000 was "not a gameplay cap" is gone — it is a recorded hard cap, and it is 400 above the highest soft cap any Global scenario currently reaches.
  - **Clarified 2026-09-27 so this bullet is not read as a constant.** `hard_caps` is a **per-scenario** field and the `scenarios` table has a `hard_cap` column to match, so the bound a `TurnEntry` is validated against is the entered run's scenario's own value, not a literal. 2000 is what that value happens to be for all four `[Global]` scenarios; the two `[JP]`-only rows carry 2500, and a future Global port of either would widen automatically. See `ADR-0003` item 6's amendment.
- **Option A remains the recommendation**, and its `scenarios` reference table now has an obvious shape: store `base`, `bonus`, and `hard_cap` per stat, not a pre-computed total, since the data itself splits them and the bonus can be negative.
  - **Withdrawn 2026-09-27, and the shipped schema already disagrees with this bullet.** `ADR-0004`'s slice built `scenarios.cap_speed … cap_wit` as five `unsignedSmallInteger` columns holding **absolute** ceilings, plus one `hard_cap`, and `GametoraScenarioParser` resolves `1200 + stats[i]` at ingestion and persists only the result. That is the right shape and this bullet is the wrong instruction, for a reason worth keeping: `base` is not in the export at all. The array is an offset from a constant the *document* inferred, so storing `base` and `bonus` would persist an inference next to a fact and invite a later reader to treat 1200 as the game's structural constant rather than as the halved-gains threshold that happens to sit under the offsets. The derivation belongs where it is verifiable and cited — the parser, with its test asserting the four Global cap sets — and the row stores the answer. If a future scenario ever needs the offset itself, add a column then; do not pre-shape the table for a number that is not published.
  - What survives this correction is the separation, not the columns: the **1,200 halved-gains threshold is a gameplay rule** and the **scenario ceiling is a different fact**, and the UI must show both as distinct markers (§6.5, D-211, D-212). Reading 1200 as a schema base would have merged them, which is the actual harm this bullet risked.
- A cap of **2500** exists on two JP-only future scenarios. Nothing in the current Global set approaches it, and building for it now would be speculation.

**One residual unknown, stated rather than smoothed over.** `hard_caps` has **six** elements while `stats` has five, and the sixth is `9999` (or `99999` on the newest JP rows). Its meaning is unverified. Do not label it "Skill Points" or any other stat on the strength of its position; if it is ever surfaced, it needs its own evidence.

**New urgency on re-verification.** The same guide records that URA Finale was reworked on **2026-07-01** on Global. The cap table in `UMAMUSUME_REFERENCE.md` §1.3.4 traces to Game8 2025-11-21 and Kamigame 2024-02-19, both already flagged `⚠️ STALE`, and the URA row is demonstrably pre-rework. Seed caps from the post-rework figures, not the table above.

**Still not retrieved.** The Unity Cup (`archives/545572`) and Trackblazer (`archives/580723`) scenario guides both redirected away from their scenario pages during extraction. Their caps in the table above remain unverified against a current source. See `CONSTRAINTS.md` §10l.

---

## Formal record, 2026-09-27

Option B, widening the stored stat bound to **0..2000**, is **accepted**. This section exists because the
approval previously lived only in session notes, so the accepted state was not in the file.

Implementation state, stated separately from the decision: `ADR-0004` landed the aptitude columns and the
`scenarios` cap table, but `StoreTurnEntryRequest` and `PRD.md` FR-C-2 still validate `0..1200`, and `US-3`
still lists "a stat outside 0..1200 is rejected" as an acceptance test. Option B is therefore **accepted and
not yet implemented**. The change is small and mechanical; the reason it is not done here is that it edits a
requirement and its stated test, which is the owner's wording to write.
