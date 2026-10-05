# ADR-0020: Trainer Desk 2.0 — SPA frontend, target-based Trainer Advisor, record-only Veteran library (race prediction stays deferred)

Status: **Accepted (owner ruling 2026-10-04).** Drafted by the Architect and recorded here because it
reverses one non-goal in full and extends two prior decisions, which `AGENTS.md` requires an ADR to do.
This file is the authorization; the subsystem designs and the build each follow in their own slice.

Date: 2026-10-04
Deciders: product owner (decision), Architect (recording and consequences)
Amends: `PRD.md` §6.2 (lifted in full)
Extends: `ADR-0001` (the §6.11 lift, widened from Energy guidance to target-based training recommendation)
Builds on: `ADR-0010` (the §6.3 recording allowance) for the Veteran library
Reaffirms: `ADR-0016` (race prediction deferred on the data blocker; §6.11 unchanged for race outcomes)
Related: `PRD.md` §6.3, §6.9, §6.11, US-3, US-4, US-10; `docs/UMAMUSUME_REFERENCE.md` §1.1, §1.5

## Context

A product-vision brief (the "Trainer Desk" / "Uma Trainer Desk" cockpit concept) was put to the owner on
2026-10-04. It describes a four-subsystem program: a scenario-driven career cockpit, a target-based
Trainer Advisor, a Legacy/Inheritance lab with a Veteran library, and an Inertia/Vue single-page frontend.
Most of the brief is either already built in a governance-compliant form (the scenario composition matrix,
the Trackblazer shop and Grade-Point panels, the Unity Cup team and spirit-burst panels, manual state
capture, event recording, the visual system) or explicitly banned by `PRD.md` §6 and accepted ADRs.

Before drafting, the current ADR set was re-read. Three of the four lifts are not clean non-goal
reversals:

- `ADR-0001` already lifted §6.11 **for Energy guidance** and authorized ranking and suggesting training
  actions with an explainability guardrail. Its owner edits to `PRD.md` and `CLAUDE.md` were never landed,
  and `CLAUDE.md`'s rewrite on 2026-10-04 removed the "Planner Domain Rules" it referenced, so that part
  of its consequence list is now moot rather than outstanding.
- `ADR-0010` already narrowed §6.3 so the "nothing more" clause caps **computation**, not **recording**.
  A record-only Veteran library fits inside it.
- `ADR-0016` is an open question the owner held on 2026-10-01 after measuring a **data blocker**: race
  distance and surface live only in `race_catalog_slots`, which is fetch-only with no seeder and holds 0
  rows offline, and `scenario_slots.tier` is 141/296 NULL. An owner scope ruling lifts the *policy*; it
  does not create the *data*.

Only the SPA frontend (`PRD.md` §6.2) is a clean reversal with no existing ADR.

## Decision

The owner ruled on 2026-10-04 to lift four areas. The ruling, recorded per area:

### 1. SPA frontend — §6.2 lifted in full

`PRD.md` §6.2 ("No SPA frontend … Blade + Tailwind v4") is reversed. The UI moves to Inertia + Vue as a
full rewrite, not a hybrid. This is the widest of the offered scopes. The rationale the brief gave for
avoiding hardcoded scenario logic is already satisfied by `config/scenarios.php` and gate G-33, so the
reversal is adopted for the interactive-cockpit interaction model, not for the architecture concern. The
cost is real and is owned here: every existing Blade view under `resources/views/` is rewritten, and the
established Blade component library and its regression-tested accessibility behavior (keyboard paths,
target sizes, review-form labels, focusable scroll regions) must be carried across, not dropped.

### 2. Trainer Advisor — `ADR-0001`'s lift widened beyond Energy

`ADR-0001` scoped its §6.11 lift to Energy guidance. This decision widens it: the tool may rank the
trainer-entered options for the next turn against a **trainer-entered build target** (purpose, distance,
surface, running style, per-stat targets, skill priorities), not only against the Energy band. Race
outcomes remain excluded (§4 below).

Every guardrail from `ADR-0001` is carried forward unchanged, because they are what keep this honest:

- Deterministic arithmetic over entered `turn_entries` plus a declared, versioned constant set; the
  constants used are visible wherever a number appears (`ADR-0001` §2's R4 replacement).
- A three-band indicator by default; a numeric estimate is opt-in, renders with its formula and parameters
  on the same surface, is labelled as this tool's model rather than the game's, and lives in config, never
  hardcoded (`ADR-0001` §3).
- Every suggestion carries a row-level derivable reason line; a suggestion with no derivable reason does
  not ship (`ADR-0001` §4).
- Where a figure cannot be sourced, it is rendered as a named absence or an `N/A`, never invented
  (`AGENTS.md` §5 Copy; `ADR-0016` candidate shape 3's rejection).

The brief's "✓ confirmed / ≈ calculated / △ RNG / ? user input" vocabulary is adopted as the display
contract for the advisor's numbers, so no figure reads as more certain than its provenance.

### 3. Veteran library — record-only, under `ADR-0010`

A Veteran library is authorized as **recording**: a completed run may be saved as a reusable parent
record, tagged and searched, so the trainer's own history feeds the next run's Legacy selection. This is
the recording half of the brief's Legacy Lab and sits inside `ADR-0010`'s allowance.

Explicitly unchanged, per `ADR-0010`: no inheritance engine. Nothing computes a Spark firing, an affinity
payout, an offspring, or an "expected inheritance." "Optimize Parents" and any auto-proposed parent
combination are computation and stay banned. The library stores and searches what the trainer entered; it
does not derive an outcome.

### 4. Race prediction — stays deferred; §6.11 unchanged for race outcomes

The owner revisited race prediction on 2026-10-04 and kept the `ADR-0016` hold. No race simulation, no
race win-probability, no race-day snapshot is built. The reason is a data blocker, not only policy: until
`race_catalog_slots` is reachable offline (a seeder plus a committed fixture) or a source states each goal
race's distance band and surface on the row the run references, any win-probability number would be an
invented statistic, which the Floor forbids regardless of scope. Closing the blocker is `ADR-0016`
candidate shape 1 and is a separate, later slice; it is not authorized by this ADR.

## Consequences

### 1. `PRD.md` moves before any build

`PRD.md` §6.2 is amended with a dated pointer here. §6.11 gains a dated note separating the widened
training-recommendation lift (§2) from the unchanged race-outcome ban (§4). New user stories and FRs for
the build target, the Trainer Advisor, and the Veteran library are added; each cites this ADR. Per
`AGENTS.md` §11, these edits are drafted for the owner to land.

### 2. OQ-5 is not resolved by this ADR

The Trainer Advisor authorization (§2) stands on its own. OQ-5 — whether the *skill screen* surfaces a
skill's running-style eligibility — is a separate product question and remains open. Nothing here ships a
skill recommendation, which OQ-5's Phase A explicitly refused.

### 3. Build order: backend/domain first, SPA rewrite carries the UI once

The advisor engine, the build-target model, and the Veteran-library schema are frontend-agnostic domain
work and can be built and tested against the current stack. The SPA rewrite (§1) then builds every UI —
existing screens and the new cockpit — once, in Vue, rather than building new screens in Blade and
rewriting them. Sequencing detail lives in the program plan, not this ADR.

### 4. No code is authorized by this ADR

This file authorizes scope only. Each subsystem gets its own design, spec, and plan (`AGENTS.md` §15,
`CLAUDE.md` §11). The schema a subsystem needs travels with its own migration, digest update, PRD
citation, and `down()`.

### 5. The accessibility contract is a carry-across obligation, not an option

The SPA rewrite must reproduce the regression-tested behavior named in `CLAUDE.md` §7 and `AGENTS.md` §13.
A rewrite that drops keyboard paths or target sizes is a regression, not a port.

## Alternatives considered

**Keep Blade; add Inertia/Vue only for the new cockpit screens.** Rejected by the owner in favour of the
full rewrite. Smaller blast radius, but leaves two frontend stacks to maintain and the catalog/runs/deck
views on the old one.

**Keep Blade entirely; build the new subsystems in the existing stack.** Recommended by the Architect and
rejected by the owner. The config-driven scenario matrix already satisfies the brief's architecture
concern, so the rewrite buys the interaction model, not a correctness improvement.

**Lift race prediction now (seed-then-predict or populated-gates-only).** Rejected by the owner; deferred
on the `ADR-0016` data blocker.

## Links

- `PRD.md` §6.2, §6.3, §6.9, §6.11, OQ-5
- `docs/adr/0001-lift-no-prediction-nongoal-for-energy-guidance.md` (§2–§4 guardrails carried forward)
- `docs/adr/0010-legacy-selection-payload.md` (§6.3 recording allowance)
- `docs/adr/0016-next-race-readiness-open-question.md` (the data blocker; candidate shapes 1 and 2)
- `docs/UMAMUSUME_REFERENCE.md` §1.1 (Energy constants), §1.5 (Sparks/affinity)
- `docs/research-scratch/GOVERNANCE.md` §"PRE-MORTEM.md" §4.1 (why the legacy prediction system was cut)
- `docs/research-scratch/PROCESS-PLANS.md`, section `## trainer-advisor.md` (subsystem 1 spec: BuildTarget + Trainer Advisor)
- `docs/proposals/screen-spec-2.0.md`, `docs/proposals/design-2.0.md` (2.0 design target, reference-only;
  deferred-computation sections carry a governance banner)
