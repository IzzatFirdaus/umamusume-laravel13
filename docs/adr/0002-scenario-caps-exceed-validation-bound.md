# ADR-0002: Scenario-aware stat caps exceed the validation bound

<!-- ADR-CONTRACT
  decision: accepted (owner ruling 2026-09-27) — option B, widen the stored stat bound to 0..2000 read from the scenario's hard_cap
  code: superseded — ADR-0015 replaced the bound with the per-stat scenario cap (App\Services\ScenarioCaps)
  superseded-by: —
  amended-by: 0015 (bound superseded), 0003 (item 6); reaffirmed unchanged by 0004, 0012 and 0025
  open: option A's per-scenario table is the shipped shape (ADR-0015); the UI condition (1,200 line + scenario ceiling as two markers) still binds as a requirement and is unbuilt — no shipped component draws either marker (2026-10-22 framework check)
-->

Status: **accepted** (owner ruling 2026-09-27). **Its validation bound is superseded by `ADR-0015`**,
which reads the per-stat scenario cap rather than a flat `hard_cap`. The UI condition below still binds.

## Context

`PRD.md` FR-C-2 fixed `TurnEntry` stats at **0..1200**, and US-3 made "a stat outside 0..1200 is
rejected" an acceptance test. `CLAUDE.md` Planner Rule 6 places the bound in `StoreTurnEntryRequest`.

`UMAMUSUME_REFERENCE.md` §1.3.4 records that every live Global scenario caps higher. **The tool could not
record a real run.** A Unity Cup Trainer reaching Wit 1350 was refused by a P0 feature, and so was a
Trackblazer Trainer at Stamina 1350. This was not a display problem; the bound itself was wrong, for every
Global player who crossed 1200 since the 2026-07-01 rework.

An earlier revision of `docs/research-scratch/DESIGN-CORPUS.md` D-31 instructed the UI to display `/1200` and
never imply a wider ceiling. That instruction was withdrawn: it dressed a schema limitation as a design
decision and would have made the defect look intentional.

## Decision

**The owner ruled option B:** validation widens to **0..2000**, sourced from the game's own `hard_caps`
field rather than an invented round number, landing alongside the `ADR-0003` schema work.

The ruling came with a UI condition that is part of the decision, not a follow-on: **the 1,200
halved-gains line and the scenario ceiling must stay two visible, differently-drawn markers.** Raising the
bound silently would trade a data defect for a comprehension defect, because 1,200 is the moment a run's
training economics change and a Trainer has to be able to see they have crossed it. `DESIGN.md` §6.5
carries the rendering requirement.

**`hard_cap` is a value, not the rule** (amended 2026-09-27, carried by `ADR-0003` item 6): the bound must
be read from the run's own scenario, with 2000 as the value that happens to apply to all four Global
scenarios today. A flat `0..2000` constant is wrong in principle because two `[JP]`-only rows carry 2500.
`ADR-0015` implements this by reading the **per-stat** cap instead, because `hard_cap` is the database
ceiling and not the in-run ceiling; `hard_cap` is kept as the clamp, not the target.

## What this does not decide

- **What the cap is composed of.** Support-card and breakthrough contributions are `PRD.md` §6.9
  territory; the UI must say what it is not accounting for (D-162), never present a partial ceiling as
  complete.
- **The `hard_caps` sixth element.** It is `9999` (or `99999` on the newest JP rows) and its meaning is
  unverified. Do not label it "Skill Points" or any stat on the strength of its position.

The four-scenario cap set is owned by `config/scenarios.php` and the `scenarios` table, and their agreement
is asserted by `tests/Feature/ScenarioCapParityTest.php`; it is not restated here.

## Options considered and rejected

- **A. Per-scenario reference table with a labelled cap stack.** The correct long-run shape and the only
  one that makes US-3 work; the owner chose B for the bound specifically so data entry stops failing while
  A stayed unfunded. **`ADR-0015` is A, funded.**
- **C. Keep 1200 and label the tool Ura-Finale-only.** Rejected: contradicts the scenario-aware
  requirement and still fails on Ura Finale above 1200.
- **D. Defer to Phase 2.** Rejected: US-3 is P0, and every mockup depicts bars the app would refuse.

## Links

- `PRD.md` FR-C-1, FR-C-2, FR-A-4, US-3, §6.9; `CLAUDE.md` Planner Domain Rules 5, 6
- `config/scenarios.php` (`base_cap`, `cap_bonus`, `hard_cap`); `App\Services\ScenarioCaps`
- `docs/UMAMUSUME_REFERENCE.md` §1.3.4, §1.5, §1.6.1; `DESIGN.md` §6.5
- `docs/research-scratch/DESIGN-CORPUS.md` D-31, D-160..D-165, G-15a..c
- `docs/framework-ui-ux/design-framework-index-2026-10-21.md` (the re-check table); rules UX-2, UI-3,
  GT-6, WCAG-2

## Ledger

- 2026-10-22 · **framework check against `docs/framework-ui-ux/` — the UI condition this ADR carries is
  unbuilt, and the present tense in three ADRs reads as a claim about a surface that is gone.** The
  re-check table asks a change that moves a progress indicator or a boundary against **UX-2** (a progress
  indicator states distance to the goal, not only the current value) and **GT-6** (a sequence keeps an
  unbroken spine); the condition above is this repository's own instance of both, and the framework
  agrees with it. **The condition is sound and it is not withdrawn — it has no surface.** The component
  that drew it, `StatBand.vue`, was deleted in the frontend cutover (`b30906a`), and the run record
  screen that hosted it was retired with it. The panel that replaced it,
  `resources/js/components/career/CareerStatePanel.vue`, is target-shaped by design and says so in its
  own header comment: the ceiling band "with the 1,200 halved-gains marker" was "deliberately not
  reused". `ScenarioCaps::ceilingsForRun()` still computes the known and the potential level, and its
  only callers are `tests/Feature/StatCeilingsTest.php`. **The supersession is recorded** in
  `docs/audits/rice-shower-unity-cup-ux-walk-resolutions.md` ("Stat-ceiling fourth marker —
  Landed-superseded") **and not in this ADR set**, which is why `ADR-0015` and `ADR-0025` still read as
  if the two markers were on screen. What the framework adds for whoever rebuilds the band: the two
  markers are **graphical objects**, so WCAG 1.4.11's 3:1 attaches to each against the surface it is
  drawn on, and they must be tellable apart **without colour** (**UI-3**), which "differently-drawn" by
  itself does not guarantee.
- 2026-09-27 · **amendment 1, after reading the URA Finale guide** — the framing treated 1200 as an
  arbitrary app constant. Two facts changed the recommendation: 1200 is a genuine in-game soft cap
  (training gains halve beyond it), and a scenario ceiling is `1200 base + scenario bonus`, a sum. The bar
  must render **two distinct markers**, the 1200 line and the scenario ceiling.
- 2026-09-27 · **amendment 2, after measuring the caps from game data** — GameTora's `scenarios` dataset
  carries `stats` (a five-element bonus array) and `hard_caps`. `stats` is a **bonus over a 1200 base**,
  not a ceiling: every row reproduces a published cap when added to 1200, and a JP row carries a
  **negative** Stamina bonus. This forced two corrections: `UMAMUSUME_REFERENCE.md` §1.3.4's Climax row is
  **transposed** (Guts/Wit read `…/1500/1200` where the data says `…/1200/1500`), and
  `SCENARIO-PUBLISHER-REFERENCES.md`'s "Global keeps flat 1200 caps" prediction was overturned by the
  2026-07-01 rework. Option B was upgraded to validate against the named `hard_caps` field.
- 2026-09-27 · **bullet withdrawn** — an amendment bullet proposed storing `base` and `bonus` per stat.
  `ADR-0004`'s slice shipped `scenarios.cap_speed … cap_wit` as **absolute** ceilings instead, and
  `GametoraScenarioParser` resolves `1200 + stats[i]` at ingestion. That is the right shape: `base` is not
  in the export, so storing it would persist an inference beside a fact. What survives is the separation of
  the 1,200 gameplay threshold from the scenario ceiling.
- 2026-09-27 · **formal record** — option B (widen the stored bound to 0..2000) is accepted. It was
  recorded because the approval previously lived only in session notes. Implementation state was separate:
  `ADR-0004` landed the cap table, but the validation change edited `PRD.md` FR-C-2 and US-3 wording, which
  was the owner's to write; `ADR-0015` later closed it.
- 2026-10-20 · **citations repointed, decision unchanged** — the Context and Links lines above cite
  `CLAUDE.md` Planner Rule 6 and `CLAUDE.md` Planner Domain Rules 5, 6. `CLAUDE.md` carried no such
  sections after `228011e` (2026-10-04), which rewrote it; the lines above are preserved as written.
  Rule 6, the 0..1200 bound in `StoreTurnEntryRequest`, is recorded in `ARCHITECTURE.md` §3, which
  carries the old rule verbatim with its `[rev 0.2 — repo #4]` tag and a dated erratum for the bound
  correction. Rule 5, that every number rendered traces to entered turns, is stated in
  `docs/research-scratch/DESIGN-CORPUS.md` P6 and required by `PRD.md` FR-C. **What this ADR decided
  is unchanged:** option B, the stored bound widens to 0..2000 sourced from the game's own `hard_caps`.
