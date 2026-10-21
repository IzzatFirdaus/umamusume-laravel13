# ADR-0020: Trainer Desk 2.0 — SPA frontend, target-based Trainer Advisor, record-only Veteran library

<!-- ADR-CONTRACT
  decision: accepted (owner ruling 2026-10-04) — four lifts: the SPA frontend (§6.2 in full), the Trainer Advisor beyond Energy, a record-only Veteran library, and race prediction stays deferred
  code: built — resources/js/ (Inertia + Vue 3); App\Services\Advisor\TrainerAdvisor; App\Models\Advisor\BuildTargetPayload; tests/Feature/CareerCockpitTest.php, TrainerAdvisorTest
  superseded-by: —
  amended-by: 0001 (its §6.11 lift is widened here), 0010 (its §6.3 recording allowance is built on), 0016 (reaffirmed, not reopened)
  open: each subsystem still needs its own design, spec and plan — this file authorizes scope only, no code
-->

Status: **Accepted** (owner ruling 2026-10-04). Drafted by the Architect and recorded here because it
reverses one non-goal in full and extends two prior decisions.

## Context

A product-vision brief was put to the owner on 2026-10-04 describing a four-subsystem program: a
scenario-driven career cockpit, a target-based Trainer Advisor, a Legacy/Inheritance lab with a Veteran
library, and an Inertia/Vue single-page frontend.

Before drafting, the current ADR set was re-read, and three of the four lifts are **not** clean non-goal
reversals:

- `ADR-0001` already lifted §6.11 **for Energy guidance** and authorized ranking and suggesting training
  actions with an explainability guardrail. Its owner edits to `PRD.md` and `CLAUDE.md` were never landed, and
  `CLAUDE.md`'s rewrite on 2026-10-04 removed the "Planner Domain Rules" it referenced, so that part of its
  consequence list is now moot rather than outstanding.
- `ADR-0010` already narrowed §6.3 so the "nothing more" clause caps **computation**, not **recording**. A
  record-only Veteran library fits inside it.
- `ADR-0016` is an open question the owner held on 2026-10-01 after measuring a **data blocker**: race
  distance and surface live only in `race_catalog_slots`, which is fetch-only with no seeder and holds 0 rows
  offline. An owner scope ruling lifts the *policy*; it does not create the *data*.

Only the SPA frontend (`PRD.md` §6.2) is a clean reversal with no existing ADR.

## Decision

The owner ruled on 2026-10-04 to lift four areas.

### 1. SPA frontend — §6.2 lifted in full

`PRD.md` §6.2 ("No SPA frontend … Blade + Tailwind v4") is reversed. The UI moves to Inertia + Vue as a full
rewrite, not a hybrid. The rationale the brief gave for avoiding hardcoded scenario logic is already satisfied
by `config/scenarios.php` and gate G-33, so the reversal is adopted for the interactive-cockpit interaction
model, not for the architecture concern. **The cost is real and is owned here:** every existing Blade view
under `resources/views/` is rewritten, and the established Blade component library and its regression-tested
accessibility behavior (keyboard paths, target sizes, review-form labels, focusable scroll regions) must be
carried across, not dropped.

### 2. Trainer Advisor — `ADR-0001`'s lift widened beyond Energy

The tool may rank the trainer-entered options for the next turn against a **trainer-entered build target**
(purpose, distance, surface, running style, per-stat targets, skill priorities), not only against the Energy
band. Race outcomes remain excluded (§4).

**Every guardrail from `ADR-0001` is carried forward unchanged**, because they are what keep this honest:
deterministic arithmetic over entered turns plus a declared, versioned constant set, with the constants used
visible wherever a number appears; a three-band indicator by default, with any numeric estimate opt-in,
formula-visible, labelled as this tool's model and living in config; a row-level derivable reason line on
every suggestion, without which it does not ship; and a named absence or `N/A` wherever a figure cannot be
sourced, never an invented one.

The brief's "confirmed / calculated / RNG / user input" vocabulary is adopted as the display contract for the
advisor's numbers, so no figure reads as more certain than its provenance.

### 3. Veteran library — record-only, under `ADR-0010`

A Veteran library is authorized as **recording**: a completed run may be saved as a reusable parent record,
tagged and searched, so the trainer's own history feeds the next run's Legacy selection. This is the recording
half of the brief's Legacy Lab and sits inside `ADR-0010`'s allowance.

**Explicitly unchanged, per `ADR-0010`: no inheritance engine.** Nothing computes a Spark firing, an affinity
payout, an offspring, or an "expected inheritance." "Optimize Parents" and any auto-proposed parent
combination are computation and stay banned. The library stores and searches what the trainer entered; it
does not derive an outcome.

### 4. Race prediction — stays deferred; §6.11 unchanged for race outcomes

The owner revisited race prediction on 2026-10-04 and kept the `ADR-0016` hold. No race simulation, no race
win-probability, no race-day snapshot is built. **The reason is a data blocker, not only policy:** until
`race_catalog_slots` is reachable offline (a seeder plus a committed fixture) or a source states each goal
race's distance band and surface on the row the run references, any win-probability number would be an
invented statistic, which the Floor forbids regardless of scope. Closing the blocker is `ADR-0016` candidate
shape 1 and is a separate, later slice; **it is not authorized by this ADR.**

## Consequences

- **`PRD.md` moves before any build.** §6.2 is amended with a dated pointer here; §6.11 gains a dated note
  separating the widened training-recommendation lift from the unchanged race-outcome ban. New user stories
  and FRs cite this ADR. Per `AGENTS.md` §11, these edits are drafted for the owner to land.
- **OQ-5 is not resolved by this ADR.** The Trainer Advisor authorization stands on its own. OQ-5 — whether
  the *skill screen* surfaces a skill's running-style eligibility — is a separate product question and remains
  open. Nothing here ships a skill recommendation, which OQ-5's Phase A explicitly refused.
- **Build order: backend/domain first, SPA rewrite carries the UI once.** The advisor engine, the
  build-target model and the Veteran-library schema are frontend-agnostic domain work. The SPA rewrite then
  builds every UI once, in Vue, rather than building new screens in Blade and rewriting them.
- **No code is authorized by this ADR.** This file authorizes scope only. Each subsystem gets its own
  design, spec and plan; the schema a subsystem needs travels with its own migration, digest update, PRD
  citation and `down()`.
- **The accessibility contract is a carry-across obligation, not an option.** The SPA rewrite must reproduce
  the regression-tested behavior named in `CLAUDE.md` §7 and `AGENTS.md` §13. A rewrite that drops keyboard
  paths or target sizes is a regression, not a port.

## Alternatives considered

- **Keep Blade; add Inertia/Vue only for the new cockpit screens.** Rejected by the owner in favour of the
  full rewrite. Smaller blast radius, but leaves two frontend stacks to maintain.
- **Keep Blade entirely; build the new subsystems in the existing stack.** Recommended by the Architect and
  rejected by the owner. The config-driven scenario matrix already satisfies the brief's architecture concern,
  so the rewrite buys the interaction model, not a correctness improvement.
- **Lift race prediction now (seed-then-predict or populated-gates-only).** Rejected by the owner; deferred
  on the `ADR-0016` data blocker.

## Links

- `PRD.md` §6.2, §6.3, §6.9, §6.11, OQ-5; `AGENTS.md` §11, §13, §15; `CLAUDE.md` §7, §11
- `ADR-0001` (§2-§4 guardrails carried forward), `ADR-0010` (the §6.3 recording allowance), `ADR-0016` (the
  data blocker; candidate shapes 1 and 2)
- `docs/research-scratch/GOVERNANCE.md` §"PRE-MORTEM.md" §4.1 (why the legacy prediction system was cut)
- `docs/research-scratch/PROCESS-PLANS.md` section `## trainer-advisor.md` (subsystem 1 spec)
- `docs/research-scratch/PLANS-AND-BRIEFS.md` section `## screen-spec-2.0.md`, `docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md` (2.0 design target, reference-only)
- `docs/UMAMUSUME_REFERENCE.md` §1.1 (Energy constants), §1.5 (Sparks/affinity)
