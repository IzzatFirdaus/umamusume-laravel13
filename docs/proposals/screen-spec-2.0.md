# Uma Trainer Desk — Screen Specification (2.0 design target)

> **Status: 2.0 design target — reference only, filed 2026-10-04. Not authorization.**
>
> Source: owner-supplied design brief (attachment, 2026-10-04), reproduced verbatim below the rule line.
> This specifies the Trainer Desk 2.0 screen set for the Inertia + Vue rewrite authorized by `ADR-0020` §1.
> It is the *target* for the SPA slice. The **current** Blade app is documented by `SCREEN_SPEC.md`; where
> the two disagree about what exists today, `SCREEN_SPEC.md` wins.
>
> **Governance holds — these sections describe computation the project has ruled deferred or banned, and
> filing this document does not authorize them:**
>
> - **SCREEN-010 / SCREEN-014** — per-training stat-yield numbers ("+62 Speed / +25 Power") and numeric
>   "Failure: 2%": no source publishes these. The approved advisor v1 excludes them
>   (`docs/research-scratch/PROCESS-PLANS.md`, section `## trainer-advisor.md` §1, §5; `ADR-0001` §3).
> - **SCREEN-011 / SCREEN-017** — race win-probability ("Win probability: 84%") and risk percentages:
>   race prediction, deferred on the `ADR-0016` data blocker; `ADR-0020` §4 reaffirms §6.11.
> - **SCREEN-006** — "expected inheritance" and "up to three recommended combinations": inheritance
>   computation. Only the record-only Veteran library is authorized (`ADR-0020` §3, `ADR-0010`).
> - **SCREEN-016** — shop-item "recommendation": the shop rotation is not modelled
>   (`config/scenarios.php` `trackblazer.shop`).
> - **§30** — numeric confidence / turn-delay quantification: a reason line is authorized
>   (`ADR-0001` §4); a numeric confidence model is not sourced.
>
> Where this target and a landed ADR conflict, the ADR wins (`AGENTS.md` §2). The deferred sections are
> kept verbatim so the design intent survives for the slice that closes their blocker.
> *Dated close-out 2026-10-08 (documentation-sync pass): the Inertia + Vue rewrite this banner says "§1
> targets" landed through Phases A–E, and each screen in this inventory shipped under the union-with-
> `design-2.0.md` mapping the plan's §4 preamble records. `SCREEN_SPEC.md` §3/§4 is the authoritative record
> of what exists (`SCR-CAR-001`–`024`, `SCR-VET-001`–`004`, `SCR-SYS-005`–`007`), and the plan's §4–§9
> close-outs record each slice. The governance holds above still bind: the win-probability, per-training
> yield, expected-inheritance, shop-recommendation and numeric-confidence sections remain unbuilt and
> render `N/A` with the blocker named, exactly as this banner states. Nothing in this file's screen set was
> changed to match what shipped — it remains the design target it was filed as.*

---

## Product direction corrections (added 2026-10-05)

A new product/UX strategy document (owner-supplied, 2026-10-05) corrects several assumptions in this brief.
This section records those corrections without rewriting the brief itself. The brief stays verbatim; the
corrections are additive notes so a reader can tell what stands and what must change before implementation.

**Core UX model shift.** The application is reoriented from "career optimizer" to **"Trainer's planning desk."**
It separates **Preparation mode** ("What am I going to build?") from **Active Career mode** ("Given what actually
happened, what should I do next?"). The app never pretends its simulated state is authoritative game state.

**Screen-level corrections:**

| Screen                             | Original assumption                     | Corrected direction                                                                                                                                                                                                                                                                                                                                                                                           |
| ---------------------------------- | --------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| SCREEN-002 (Scenario Selection)    | Three scenarios with difficulty stars   | **Four Global scenarios**; remove star ratings; show optimization focus (primary/secondary/training complexity); Grand Concert marked "PARTIALLY DOCUMENTED"                                                                                                                                                                                                                                                  |
| SCREEN-005 (Build Target)          | Generic stat targets                    | Add **Career Plan object**: purpose, scenario, trainee, race profile, stat/aptitude/style/skill targets, legacy/support requirements, risk tolerance. Render as "Your target" not "The correct target"                                                                                                                                                                                                        |
| SCREEN-006 (Legacy Lab)            | Two-parent picker                       | Upgrade to **six-node ancestry planner** (Parent A/B + four grandparents). Show Spark probabilities (`~10% ★★★`), not guarantees. Distinguish Blue/Pink/Green/White/Scenario Sparks. Optimize entire ancestry configuration. **Factor yield clarified 2026-10-05**: exactly 1 Blue + 1 Pink per run, at most 1 Green (requires 3★ parent), White sparks unbounded — model as variable yield, not a slot cap   |
| SCREEN-007 (Support Deck)          | Five owned + one borrowed               | **Six slots**, ownership flag (OWNED/RENTED). **Seven support types**: Speed, Stamina, Power, Guts, Wit, Pal, Group. Scenario Link derived from scenario+character, not stored on card. Granular deck analysis (training power, early run, race, safety, events, skills)                                                                                                                                      |
| SCREEN-008 (Run Preflight)         | Simple validation                       | Rename internally to **"Career Contract"**: scenario, trainee, six Legacy members, sparks analyzed, support deck, race profile, stat/skill/aptitude targets, risk profile. Warnings for low factor probability, missing aptitudes                                                                                                                                                                             |
| SCREEN-009 (Career Cockpit)        | Dashboard                               | Make it a **state machine**: explicit `CareerState` (year, half, turn, energy, mood, stats, SP, fans, bonds, races, events, goals, scenario_state, inheritance_state, action_history). Every action: BEFORE → USER ENTERS → EXPECTED → ACTUAL → UPDATED STATE → RECALCULATED                                                                                                                                  |
| SCREEN-010 (Training Decision)     | "+62 Speed / +25 Power" yields          | Per-training yields are **unsourced** (§1.1.1 ⚠️ STALE). Render `N/A`. Keep decision card structure but remove specific yield numbers                                                                                                                                                                                                                                                                         |
| SCREEN-011 (Race Decision)         | Win probability percentage              | Race prediction **deferred** (`ADR-0016`). Use conservative readiness bands (Excellent/Good/Borderline/Poor), not fake precision ("Win probability: 84%"). Model race as complex object (surface, distance, band, style, grade, venue, layout, corners, straights, elevation, weather, ground, season, time, fans, skill interactions, scenario effects)                                                      |
| SCREEN-012 (Event Decision)        | Simple choice recording                 | Model event as: source (Support/Character/Scenario/Random), choices, known outcomes, current career state, expected effect, recommendation. If outcome incomplete: "⚠ Event outcome incomplete — choose manually"                                                                                                                                                                                             |
| SCREEN-013 (Inheritance Event)     | Expected inheritance display            | Show **probability, not guarantees**. Factor outlook: `Speed ★★★ Potential payout: +21 Estimated roll: ~10%`. NOT "guaranteed"                                                                                                                                                                                                                                                                                |
| SCREEN-014–017 (Scenario Panels)   | Static modules                          | Each panel genuinely different: URA (goals + Happy Meek), Unity Cup (**Team Cockpit** with team rank/spirit/bursts), Trackblazer (Grade Points + shop + rivals + Twinkle Star Climax), Grand Concert (basic tracker only, advanced advisor limited)                                                                                                                                                           |
| SCREEN-018 (Career Timeline)       | Turn log                                | Record **decisions, not just turns**: BEFORE state, ACTION, EXPECTED result, ACTUAL result, RESULT, DECISION accepted/rejected, ADVISOR CONFIDENCE. Creates career audit trail                                                                                                                                                                                                                                |
| SCREEN-019 (Career Result)         | Summary report                          | Upgrade to **Veteran Creation screen**: career result, aptitudes, skills, race history, sparks, legacy value (factor quality assessment), best use recommendation                                                                                                                                                                                                                                             |
| SCREEN-020 (Save Veteran)          | Simple save                             | Add **Factor Analysis** workflow: veteran review → factor analysis → legacy value → tag → save. Recommendation: "Excellent Medium parent. Best used for: Medium, Pace Chaser, Speed-oriented builds"                                                                                                                                                                                                          |
| SCREEN-021 (Veteran Library)       | List view                               | Three views: **Veterans** (all completed careers), **Factors** (searchable factor inventory), **Ancestry** (see where Veteran came from — six-node graph). Add "Find Parents for This Build" button                                                                                                                                                                                                           |
| SCREEN-022 (Veteran Comparison)    | Side-by-side stats                      | Add factor comparison, ancestry visualization, compatibility calculation. Search by factor requirements, aptitude requirements, skill requirements, compatibility, scenario requirements                                                                                                                                                                                                                      |
| New (not numbered)                 | —                                       | Add **Race Planner sophistication**: course analysis (final corner, slope, backstretch, final straight, last-spurt conditions, position triggers). Don't hard-code distance ranges without resolving conflicts (1400m: Game8=Sprint vs export=Mile)                                                                                                                                                           |

**New interaction patterns:**

- **Undo last recorded action**: Safe because local planning tool, not game manipulation
- **Screenshot-assisted entry** (future): Import screenshot → OCR → user confirmation → store as `USER_CONFIRMED_SCREENSHOT`
- **Three complexity levels**: Beginner ("What should I do?" → "Train Speed"), Intermediate (+ why), Advanced (full modifier breakdown)
- **Progressive disclosure**: Default shows summary; expand for training calculation, support contribution, scenario modifier, race requirements, source/confidence

**Data integrity layer additions:**

Every important game mechanic represented as `GameRule` with: key, scenario_id, server, version, value, source, source_type, confidence, verified_at, notes. Example:

```text
scenario.trackblazer.stat_cap.stamina
value: 1900
server: global
source: gametora_scenario_export
confidence: verified
verified_at: 2026-09-27
```text

**Terminology authority:** Global terminology is authoritative (Scouts, Transfer Requests, Veteran Umamusume, Pal, Wit, Inspiration, Legacies, Sparks). UI always uses Global labels; internal keys separate. JP-only mechanics explicitly excluded (four Global scenarios vs fourteen JP; JP-only features like Independent Training marked unverified for Global).

**Confidence model:** Every recommendation carries confidence level: HIGH (supported by current client/data + current source), MEDIUM (current data + older guide), LOW (derived from incomplete information), UNVERIFIED (do not use for automatic recommendation). Example: "Train Speed — Confidence: HIGH" vs "Predicted exact stamina for 2200m — Confidence: LOW" (no verified metres-per-Stamina conversion).

**Multi-objective optimizer architecture:** Hard constraints (mandatory race within N turns, fan requirements) come before optimization (career goals, scenario objectives, build targets, legacy value, risk model). LLM explains recommendation, does not invent it.

**Versioning mandatory:** Every career run preserves ruleset snapshot. Old Veteran says "Ruleset: Global Trackblazer v2026-04" not silently recalculated with today's rules. Data status indicator on dashboard: "GLOBAL DATA ● Current Updated: 2026-09-27" or "⚠ Snapshot Last verified: 2026-09-27".

**Strongest differentiator clarified:** Not "calculates training gains" (existing calculators do that). It is: **"Understands relationship between current Career Plan, six-person Legacy configuration, Support Deck, current Career State, and the Veteran being created."**

---

## Knowledge grounding (added 2026-10-05)

The brief numbers its own screens `SCREEN-001` … `SCREEN-027`. The repository numbers screens
`SCR-<AREA>-<nnn>` (`SCREEN_SPEC.md` §2). This section maps the two so a slice can cite one ID, and
records what the corpus holds for each screen's data. It does not edit the brief.

**Terminology.** "July 2026 rebalance" → the corpus's **2026-07-01 Global rework**
(`docs/UMAMUSUME_REFERENCE.md` L1016; `SCENARIO-PUBLISHER-REFERENCES.md` L544). "Support Decks" in the
brief's nav (§3) is the repository's **Support Cards** (`SCREEN_SPEC.md` §3 nav order).

**Screen mapping.** This document's screens are `SCREEN-001` … `SCREEN-024`. `design-2.0.md` carries a
**different** inventory (`SCR-001` … `SCR-027`) that adds a Skills Planner and a Grand Concert panel and
omits Inheritance Event, Career Timeline and Veteran Comparison. The two lists disagree; neither is
authoritative over the other, and the development plan builds their union. "Port" = the screen exists today
and this rewrite re-renders it in Vue; "New" = no current screen.

| Brief ID     | Screen                        | Repo ID                                                   | Route / source                                                                   | State                                                            |
| ------------ | ----------------------------- | --------------------------------------------------------- | -------------------------------------------------------------------------------- | ---------------------------------------------------------------- |
| SCREEN-001   | Dashboard                     | — (SPA `Dashboard.vue`)                                   | `GET /` (`home`)                                                                 | Partly built; Active-Career/Recent-Veterans are named absences   |
| SCREEN-002   | Scenario Selection            | New                                                       | `config/scenarios.php`                                                           | New; binds the four-scenario matrix                              |
| SCREEN-003   | Trainee Selection             | `SCR-CAT-001`                                             | `GET /umamusume`                                                                 | Port                                                             |
| SCREEN-004   | Trainee Profile               | `SCR-CAT-002`                                             | `GET /umamusume/{slug}`                                                          | Port                                                             |
| SCREEN-005   | Build Target                  | New (`FR-F`)                                              | `training_runs.build_target`                                                     | New; spec `PROCESS-PLANS.md` `## trainer-advisor.md` §2          |
| SCREEN-006   | Legacy Lab                    | New (`FR-G`, record-only)                                 | Veteran library                                                                  | New; optimization parts **held** (`ADR-0020` §3)                 |
| SCREEN-007   | Support Deck Builder          | New                                                       | `runs.deck.sync`; `x-deck-editor`                                                | New screen over an existing write path                           |
| SCREEN-008   | Run Preflight                 | New                                                       | composes 005–007                                                                 | New                                                              |
| SCREEN-009   | Career Cockpit                | New (descends `SCR-RUN-003`)                              | `GET /training-runs/{run}`                                                       | New; the flagship screen                                         |
| SCREEN-010   | Training Decision             | New (`FR-F`)                                              | advisor spec                                                                     | New; per-training yields **held**                                |
| SCREEN-011   | Race Decision                 | New                                                       | `RaceCatalogSlot`                                                                | New; win-probability **held** (`ADR-0016`)                       |
| SCREEN-012   | Event Decision                | New                                                       | `TurnEvent` + `TurnEvents\*Payload`                                              | New over existing event recording                                |
| SCREEN-013   | Inheritance Event             | New                                                       | —                                                                                | New; **record-only** (`ADR-0020` §3)                             |
| SCREEN-014   | Scenario Panel: URA Finale    | New                                                       | `ura_finale`                                                                     | New                                                              |
| SCREEN-015   | Scenario Panel: Unity Cup     | New                                                       | `unity_cup`; `x-spirit-burst-roster`, `x-team-rank-gauge`, `x-team-race-panel`   | New over existing components                                     |
| SCREEN-016   | Scenario Panel: Trackblazer   | New                                                       | `trackblazer`; `x-shop-panel`, `x-grade-point-meter`, `x-epithet-checklist`      | New; shop recommendation **held** (rotation not modelled)        |
| SCREEN-017   | Scenario Race Planner         | New                                                       | `RaceCatalogSlot`                                                                | New; win-probability **held** (`ADR-0016`)                       |
| SCREEN-018   | Career Timeline               | New                                                       | `TurnEntry` / `TurnEvent`                                                        | New                                                              |
| SCREEN-019   | Career Result                 | New                                                       | `TrainingRun`                                                                    | New                                                              |
| SCREEN-020   | Save Veteran                  | New (`FR-G`)                                              | —                                                                                | New                                                              |
| SCREEN-021   | Veteran Library               | New (`FR-G`)                                              | —                                                                                | New                                                              |
| SCREEN-022   | Veteran Comparison            | New (`FR-G`)                                              | —                                                                                | New                                                              |
| SCREEN-023   | Database                      | `SCR-CAT-001/002`, `SCR-SKL-001/002`, `SCR-SUP-001/002`   | `GET /umamusume`, `/skills`, `/support-cards`                                    | Port; races and scenarios are new                                |
| SCREEN-024   | Settings                      | `SCR-SYS-002`                                             | `GET /preferences`                                                               | Ported to Vue 2026-10-04                                         |

**From `design-2.0.md` but not here:** a Skills Planner (`SCR-013`) and a Grand Concert panel (`SCR-017`).
Both are in the development plan's union. Grand Concert's panel is a baseline strip only (below).

**Not in either brief but already shipping:** the Review queue (`SCR-REV-001`, `GET /review`, ported to Vue)
and the run import screens (`SCR-RUN-004`/`005`). They stay.

**Corpus authority per screen's data.** Scenario panels read `config/scenarios.php`, whose per-number
provenance is stated in its own header (`GameTora scenarios.json`, verified 2026-09-27). Sparks/Affinity
data (006, 013, 021) is `UMAMUSUME_REFERENCE.md` §1.5. Support-card effects (007, 022) are §1.4. The
Trackblazer shop catalogue and epithet routes are transcribed from `SCENARIO-PUBLISHER-REFERENCES.md`.

**Unknowns the plan must render as absence, never invent:** Grand Concert mechanics (07 stub, §2.2.4
❌ UNVERIFIED); race distance/surface on goal races and any win probability (`ADR-0016`); per-training
stat yields and failure % (§1.1.1 ⚠️ STALE, excluded by the advisor spec); the official scenario title
"Twinkle Star Climax" (§7 conflict row 31 ❌ UNVERIFIED as the official name; "Trackblazer" is the export
label); the factor-slot count (§1.5 L641 ❌ UNVERIFIED); and the `app.ruleset` string, currently `null`.

---

# Uma Trainer Desk — Screen Specification

**Project:** Uma Trainer Desk
**Platform:** Local-only web application
**Stack:** Laravel 13 + Inertia.js + Vue 3
**Primary use:** Umamusume: Pretty Derby career planning and run management
**Target server:** Global English
**Primary scenarios:** URA Finale, Unity Cup, Trackblazer
**Future-compatible:** Additional scenarios such as Brighter Together: Our Grand Concert

---

## 1. Product Definition

Uma Trainer Desk is a local-first Trainer companion application for planning,
tracking, and analyzing Umamusume career runs.

The application does NOT attempt to play the game automatically.

It assists the player by:

- planning a career before starting
- evaluating trainee builds
- comparing inheritance candidates
- building support card decks
- tracking career state
- comparing available actions
- explaining recommendations
- tracking scenario-specific mechanics
- recording the completed career
- maintaining a Veteran/Legacy library

The user remains the final decision-maker.

---

# 2. Primary User Flow

```text
Dashboard
    │
    ▼
New Career
    │
    ▼
Scenario Selection
    │
    ▼
Trainee Selection
    │
    ▼
Build Target
    │
    ▼
Inheritance / Legacy Lab
    │
    ▼
Support Deck Builder
    │
    ▼
Run Preflight
    │
    ▼
Career Cockpit
    │
    ├── Training
    ├── Race
    ├── Rest
    ├── Recreation
    ├── Scenario Action
    ├── Event
    └── Inheritance
    │
    ▼
Career Result
    │
    ▼
Save Veteran
    │
    ▼
Veteran / Legacy Library
```text

---

# 3. Application Shell

All screens except the initial setup screens use the Application Shell.

```text
┌──────────────────────────────────────────────────────────┐
│ Logo / App Name                  Current Career / Profile │
├──────────────┬───────────────────────────────────────────┤
│              │                                           │
│ Navigation   │               Page Content                │
│              │                                           │
│ Dashboard    │                                           │
│ New Career   │                                           │
│ Legacy Lab   │                                           │
│ Support      │                                           │
│ Veterans     │                                           │
│ Database     │                                           │
│ Settings     │                                           │
│              │                                           │
├──────────────┴───────────────────────────────────────────┤
│ Status / Local Data / Version                            │
└──────────────────────────────────────────────────────────┘
```text

## Global navigation

- Dashboard
- New Career
- Legacy Lab
- Support Decks
- Veterans
- Database
- Settings

When a career is active, a dedicated Career navigation becomes available.

---

# 4. SCREEN-001 — Dashboard

## Purpose

Landing screen for returning users.

## Primary goals

- resume active career
- start new career
- inspect recent veterans
- access legacy tools
- see application/data status

## Layout

```text
┌──────────────────────────────────────────────────────────┐
│ Uma Trainer Desk                                         │
│                                                          │
│ Welcome back, Trainer.                                  │
│                                                          │
│ ┌──────────────────────────────────────────────────────┐ │
│ │ ACTIVE CAREER                                        │ │
│ │                                                      │ │
│ │ Tokai Teio                                           │ │
│ │ Trackblazer · Senior Year                            │ │
│ │                                                      │ │
│ │ Turn 37 · Energy 72 · Grade Points 280              │ │
│ │                                                      │ │
│ │ [Resume Career]                                      │ │
│ └──────────────────────────────────────────────────────┘ │
│                                                          │
│ [New Career]   [Legacy Lab]   [Support Decks]            │
│                                                          │
│ Recent Veterans                                          │
│ Recent Builds                                            │
└──────────────────────────────────────────────────────────┘
```text

## Components

- ActiveCareerCard
- NewCareerButton
- RecentVeterans
- RecentBuilds
- DataStatus
- QuickActions

## Empty state

If no active career exists:

> No active career. Start a new training run.

---

# 5. SCREEN-002 — Scenario Selection

## Purpose

Choose the career scenario.

## Scenario cards

Each card must display:

- scenario name
- short description
- complexity
- primary mechanic
- recommended use
- availability
- ruleset version

## Current scenarios

### URA Finale

Primary mechanic:

- standard career progression
- character goals
- Happy Meek system

### Unity Cup

Primary mechanic:

- team development
- team races
- Spirit
- Spirit Bursts
- Extreme Spirit Bursts

### Trackblazer

Primary mechanic:

- Grade Points
- Shop Coins
- Pro Shop
- Rival races
- Twinkle Star Climax

## Future scenarios

Future scenarios must be represented through the same Scenario interface.

The UI must not assume that every scenario uses:

- the same objectives
- the same currencies
- the same finale
- the same training rules

---

# 6. SCREEN-003 — Trainee Selection

## Purpose

Select the trainee for the career.

## Features

- searchable roster
- sorting
- filtering
- trainee comparison
- detailed trainee profile

## Filters

- Surface
- Distance
- Running style
- Aptitude
- Growth rate
- Scenario suitability
- Unique skill
- Character

## Trainee card

```text
┌──────────────────────────────────────┐
│ Tokai Teio                          │
│                                      │
│ Turf       A                         │
│ Sprint     F                         │
│ Mile       E                         │
│ Medium     A                         │
│ Long       B                         │
│                                      │
│ Pace Chaser A                        │
│                                      │
│ Growth                                     │
│ Speed +20%                              │
│ Power +10%                              │
│                                      │
│ [View Profile] [Select]              │
└──────────────────────────────────────┘
```text

---

# 7. SCREEN-004 — Trainee Profile

## Purpose

Provide complete build-relevant information before selection.

## Sections

### Basic

- Name
- Rarity
- Version
- Growth rates

### Aptitudes

- Surface
- Distance
- Running style

### Skills

- Unique skill
- Starting skills
- Awakening skills
- Event skills

### Career goals

Display the trainee's expected career objectives.

### Build analysis

Show:

- ideal distances
- suitable running styles
- recommended stat distribution
- useful inheritance
- useful support types

---

# 8. SCREEN-005 — Build Target

## Purpose

Define what the player wants from the run.

## Target purpose

Options:

- Story Clear
- Competitive Build
- Champions Meeting
- Parent Farming
- Skill Farming
- General Training

## Race profile

- Surface
- Distance
- Running style

## Target stats

```text
Speed       1200
Stamina      700
Power       1000
Guts         400
Wit          900
```text

## Skill priorities

Each skill may be marked:

- Required
- High priority
- Optional
- Ignore

## Target summary

The screen must generate a human-readable summary:

> Build Tokai Teio for Turf Medium-distance Pace Chaser.
> Prioritize Speed, Power and Wit.
> Focus inheritance on Medium and Pace Chaser aptitude.

---

# 9. SCREEN-006 — Legacy Lab

## Purpose

Select and optimize inheritance.

This is a primary feature of the application.

## Layout

```text
┌──────────────────────────────────────────────────────────┐
│ LEGACY LAB                                               │
├───────────────────────┬──────────────────────────────────┤
│ Candidate Veterans    │ Current Build                    │
│                       │                                  │
│ Search                │ Trainee                          │
│ Filters               │       ↓                          │
│                       │ Parent A      Parent B            │
│ Veteran cards         │       ↓          ↓                │
│                       │ Grandparents                     │
│                       │                                  │
│                       │ Inheritance Analysis             │
└───────────────────────┴──────────────────────────────────┘
```text

## Candidate filters

- Blue Sparks
- Pink Sparks
- Green Sparks
- White Sparks
- Distance
- Surface
- Running style
- Skills
- Race history
- Scenario factor
- Affinity

## Parent slots

- Parent A
- Parent B

## Grandparent visualization

Show:

```text
Parent A
├── Grandparent A1
└── Grandparent A2

Parent B
├── Grandparent B1
└── Grandparent B2
```text

## Compatibility analysis

Display:

- affinity
- expected inheritance
- useful Sparks
- missing Sparks
- race compatibility
- skill coverage

## Recommendation

Provide up to three candidate combinations.

Each recommendation must include a reason.

Example:

> Recommended because it provides the strongest Medium aptitude coverage
> while retaining the required Speed and skill Sparks.

---

# 10. SCREEN-007 — Support Deck Builder

## Purpose

Build the six-card support deck.

## Slots

- five owned cards
- one borrowed card

## Layout

```text
┌──────────────────────────────────────────────────────────┐
│ SUPPORT DECK                                             │
│                                                          │
│ [Speed] [Speed] [Power] [Wit] [Wit] [Borrowed]          │
│                                                          │
├──────────────────────────────────────────────────────────┤
│ DECK ANALYSIS                                            │
│                                                          │
│ Training        ████████░░                              │
│ Skill Coverage  █████████░                              │
│ Race Bonus      ██████░░░░                              │
│ Bond Potential  █████████░                              │
│ Scenario Fit    ████████░░                              │
└──────────────────────────────────────────────────────────┘
```text

## Filters

- Type
- Rarity
- Level
- Limit Break
- Skill
- Training bonus
- Race bonus
- Scenario compatibility

## Deck analysis

Must explain:

- strengths
- weaknesses
- skill coverage
- stat coverage
- scenario compatibility
- recommended replacement

---

# 11. SCREEN-008 — Run Preflight

## Purpose

Final validation before creating a career run.

## Sections

### Build

- trainee
- scenario
- target
- inheritance

### Support deck

- six cards
- deck analysis

### Target

- stats
- skills
- race profile

### Warnings

Examples:

- Missing distance aptitude
- Weak stamina plan
- Low skill coverage
- Poor support synergy
- Missing scenario requirement

## Actions

- Back
- Edit Legacy
- Edit Deck
- Edit Target
- Start Career

---

# 12. SCREEN-009 — Career Cockpit

## Purpose

Primary screen used throughout the career.

This is the application's most important screen.

## Layout

```text
┌─────────────────────────────────────────────────────────────┐
│ Career Header                                               │
├──────────────┬──────────────────────────────┬───────────────┤
│ Timeline     │ Current Turn                 │ Advisor       │
│              │                              │               │
│ Career       │ Current stats                │ Best Action   │
│ milestones   │ Training options             │ Why           │
│              │ Race                         │ Projection    │
│              │ Scenario action              │ Alternatives  │
│              │                              │               │
├──────────────┴──────────────────────────────┴───────────────┤
│ Scenario Panel / Upcoming Events / Race Calendar            │
└─────────────────────────────────────────────────────────────┘
```text

## Header

Show:

- scenario
- year
- month
- turn
- energy
- mood
- fans
- skill points
- scenario resources

## Next Decision Component (new spec)

The Advisor column is a shared component, not a screen variant. It receives scenario-specific objectives, constraints, and rules via props — never by branching on scenario name.

**Required behavior:**

- Display the current objective and the reason it matters now.
- Present a recommended action only when sufficient verified rules and state support it.
- Explain the decisive factors and relevant trade-offs (constraints, opportunity cost, missing information).
- Show viable alternatives where the available data permits a meaningful comparison.
- Distinguish recorded facts, calculated results, estimates, and unknown values using the trust vocabulary (Confirmed / Calculated / Estimated / Unknown, per `ADR-0020` §2).
- Let the user choose another action without being blocked by the recommendation.
- Record the action, expected consequences where supportable, and actual result when entered.
- Re-evaluate the next decision after the run state changes.

**Explicit states:**

```
No action selected yet
  → "No recommendation — insufficient verified rules or state to rank actions"

Action under review
  → show forecasted consequences with provenance labels

Action recorded, actual result pending
  → grey out, show "ENTER ACTUAL RESULT"

Action recorded, actual result entered
  → show reconciliation (expectation vs. observation)

No eligible action
  → "No viable action available — all options violate a constraint"
```

---

# 13. Career State Panel

Always display:

```text
Speed
Stamina
Power
Guts
Wit

Energy
Mood
Fans
Skill Points
```text

Stat targets should appear as progress bars.

---

# 14. SCREEN-010 — Training Decision

## Purpose

Compare available training actions.

## Training card

```text
SPEED

Facility level: 3 (repetition: 12/16)
Energy cost: 19

Support: 3 flames
Bond gains: +7 / +7 / +5

Target impact:
Speed: 742 / 800 (93%)

Missing data: exact stat yield (+X Speed), failure probability
```

**No per-training stat yields.** Numeric training gains are unsourced (`ADR-0020` §3, deferral table line 13-14). The card explains the factors used to compare actions without showing unsupported yield or failure numbers.

## Actions

- Train
- Inspect details

## Details

Display:

- facility level and progress to next
- energy cost
- support flame count
- bond gains (per support card, where recorded)
- scenario effects (where verified, e.g. Happy Meek duel on Stamina)
- target impact (distance to goal, with provenance label)

---

# 15. SCREEN-011 — Race Decision

## Purpose

Select whether and where to race.

## Race cards

Display:

- race name
- grade
- distance
- surface
- running style
- expected reward (Confirmed / Calculated / Estimated)
- fan gain
- skill point gain
- scenario reward (if sourced)

**No numeric win probability.** Race prediction is deferred on the `ADR-0016` data blocker (`ADR-0020` §4). Instead, show readiness bands using verified stat comparisons:

```
READINESS BANDS

EXCELLENT  Your stat profile matches the race requirements
GOOD       Minor deficits, win is plausible with skill support
BORDERLINE Key stat below the distance-appropriate floor
POOR       Stamina or Speed well below the distance floor

Missing data: opponent strength, exact placement modifiers
```

## Risk indicator

```text
LOW       No fatigue chain, full energy
MEDIUM    2-consecutive race chain (see §10 Race Fatigue)
HIGH      3+ consecutive races (fatigue event likely)
```

The exact thresholds come from the race-fatigue table (`SCENARIO-PUBLISHER-REFERENCES.md §7`); they are Trackblazer-specific and must not be hardcoded in the component.

---

# 16. SCREEN-012 — Event Decision

## Purpose

Record event choices and their effects.

## Layout

```text
EVENT

Kitasan Black

Option A
+20 Speed
+10 SP

Option B
+30 Energy
+10 Mood

────────────────────

Trainer Advisor

Recommended: Option B

Reason:
Energy is currently low and the next
important race is approaching.
```text

The user must be able to override the recommendation.

---

# 17. SCREEN-013 — Inheritance Event

## Purpose

Track inheritance milestones during the career.

## Display

- parent Sparks
- grandparent Sparks
- expected inheritance
- previous inheritance
- newly activated Sparks

## Timeline

```text
Career Start
    ✓

Classic April
    ○

Senior April
    ○
```text

---

# 18. SCREEN-014 — Scenario Panel: URA Finale

## Purpose

Expose URA-specific information.

## Components

- character goals
- URA progression
- Happy Meek status
- finale preparation
- upcoming mandatory races

## Happy Meek

Show:

- current level (wins / 6)
- duel availability (next training turn)
- potential reward (stat, cap bump, Racing Spirit hint, SP)
- final-race contribution (powered-up state at 6 wins → Past My Limits)

## Career Milestone Tracker (new)

A milestone strip showing the current career objective, deadline, completion state, and next required step:

```
MILESTONES

[ ✓ ] Valentine's gate (60k fans)    completed, turn 8
[ • ] Fan Fest gate (70k fans)       32k / 70k, 18 turns left
[ ○ ] Holiday gate (120k fans)       upcoming, 42 turns left
[ • ] URA Qualifier                scheduled, turn 36
[ ○ ] URA Semifinals               lock after Qualifier win
[ ○ ] URA Finals                   lock after Semifinals win
```

Source: `docs/scenarios/01-ura-finale.md:77-84`, `UMAMUSUME_REFERENCE.md §2.2.1`.

## Readiness Summary (new)

Assess race preparation using verified data only:

```
READINESS FOR URA QUALIFIER (Scheduled: turn 36)

Distance    Medium    ✓ Stamina 610 ≥ 600 floor
Surface     Turf      ✓ aptitude B
Stat target Speed 800  ⚠ 742 / 800
Skills      Last spurt  ✓ owned
            Power surge  ⚠ hint unlocked, not purchased

Missing data: exact qualifier distance (determined by most-raced type)
```

## Training-vs-Recovery Decision Area (new)

Explain the current priority and known trade-offs: train, rest, or race. Each option shows its primary constraint and what it preserves for later:

```
RECOMMENDED: TRAIN STAMINA
  Supports: Medium-distance floor ahead of Qualifier
  Constraint: facility at Lv 2, 1 block from Lv 3
  Trade-off: -40 energy; Summer camp in 6 turns

[ Train Stamina ]   [ Race a Tier 2 (SP: +40) ]   [ Rest ]
```

## Pre-Final Review (new)

Before the three finale races, identify recorded strengths, known deficits, and unresolved requirements:

```
PRE-FINALE READINESS

Strengths:   Speed 920+, Stamina 650+, Guts 700+
Deficits:    Power 580 (below 600 Medium floor)
Unresolved:  Qualifier distance (tied to most-raced type)
```

---

# 19. SCREEN-015 — Scenario Panel: Unity Cup

## Purpose

Manage scenario-specific team progression.

## Components

- team rank
- team member stats
- team composition
- team races
- Special Training
- Spirit
- Spirit Burst
- Extreme Spirit Burst

## Team management view (enhanced)

A team roster dashboard that tracks members, their relevant strengths, development needs, and contribution to upcoming competitions. Show each member's aptitude and available skills, and distinguish individual trainee development from team-level progression:

```
TEAM ROSTER                                Next Cup: 4 turns

Member          Stat focus   Rank  Needs before Cup        Contribution
Rice Shower      Stamina     B     +30 Sta for race 2     Burst: Stamina tile
Fine Motion      Speed       A     —                      Burst: Speed tile (Lv 2)
Biwa Hayahide    Power       C     +50 Power, +2 wins     Epithet: Goddess route
Silence Suzuka†   Guts       D     Bond 2 bars            Burst: Guts tile
Mejiro Ryan†      Wit        C     +30 Wit                Burst: Wit tile

† Story characters (no bond gauge, no Friendship training)
```

Source: `docs/scenarios/02-unity-cup.md`, `SCENARIO-PUBLISHER-REFERENCES.md §7`.

## Tournament preparation (new)

Show known requirements, relevant rival information, and unresolved readiness concerns. Explicit missing-data states when the opponent, matchup, or team calculation is unavailable:

```
TOURNAMENT READINESS              Classic late June • Next Cup in 4 turns

OPPONENT PROJECTIONS
  ○ Strong     rank if won: A+         ⚠ Rice Shower needs +30 Stamina
  ○ Medium     rank if won: B          ✓ Biwa's Power is borderline
  ○ Weak       rank if won: C

RACE PREVIEW
  Sprint     ✓ covered (Fine Motion +20 from burst)
  Mile       ⚠ borderline (Fine Motion +30 needed)
  Medium     ✓ covered
  Long       ✓ covered
  Dirt       ⚠ Mejiro Ryan bond not ready

Status: Insufficient data for opponent strength projection
```

## Spirit panel

Display:

- current Spirit
- burst readiness (gauge vs. configured threshold)
- recommended timing (held advice — see `ADR-0020` §3)
- projected benefit (held advice — see `ADR-0020` §3)

---

# 20. SCREEN-016 — Scenario Panel: Trackblazer

## Purpose

Manage Trackblazer-specific resources.

## Components

- Grade Points
- Shop Coins
- Pro Shop
- purchased items
- Rival races
- race schedule
- Twinkle Star Climax

## Race calendar (new)

Visualize upcoming races, known objectives, and the current preparation window. Chronological view with distance, surface, grade, and consequence of selection:

```
RACE CALENDAR                    Classic • Turn 31

UPCOMING (next 6 turns)
  Turn 34  G2 Mile (Turf)        +80 GP, +100 coins  ✓ in plan
  Turn 35  G3 Sprint (Turf)      +60 GP, +80 coins    ✓ in plan
  Turn 36  G1 Long (Turf)        +100 GP, +150 coins  ⚠ tight spacing

CONSEQUENCES OF SKIPPING
  G2 Mile: -80 GP from runway, -2 route progress
  G3 Sprint: no objective impact
  G1 Long: -100 GP, epithet route at risk

Status: 128 GP needed, 220 projected (with wins)
```

Source: `SCENARIO-PUBLISHER-REFERENCES.md §7`, `docs/Trackblazer-plan.md §6`.

## Resource ledger (new)

Track Grade Points, purchased items, and remaining balances. Distinguish actual balances from planned spending and hypothetical spending:

```
RESOURCE LEDGER

GRADE POINTS
  Current:        172 / 300 (Classic period)
  Projected:      392 (2 G2 wins + 1 G3 win)
  Shortfall:      none

COINS
  Current:        210
  Planned spend:  100 (Summer items)
  Reserve:        150 (final shop)

ITEMS
  Good-Luck Charm  1/5   ✓ held
  Master Cleat     1/5   ⚠ use before G1 Long
  Empowering Meg.  2/5   ✓ Summer stock
```

## Race-versus-training comparison (new)

Make the opportunity cost of each option explicit — not just GP vs. stats, but fatigue, recovery, and epithet progress:

```
RACE VS. TRAINING

Race G2 Mile:        +80 GP, +100 coins, 1 fatigue, -2 route progress if skipped
Train Speed:         +6 Speed, +3 Power, +5 SP, facility +0.25 progression
Rest:                clears fatigue chain, -1 turn of GP

ADVISOR: Race G2 Mile — you are 128 GP short with 9 turns left.
         Training Speed gains less than the checkpoint risk costs.
```

## Schedule risk warnings (new)

Flag potentially unsustainable race sequences, inadequate recovery, and missed objective opportunities, using only supported data:

```
SCHEDULE RISKS

⚠ 5 consecutive races planned in Classic spring (fatigue risk)
⚠ Year-end gate: 60k fans + Akikawa bond 31 needed
○ Rival races are random; cannot be scheduled
```

Source: fatigue table `SCENARIO-PUBLISHER-REFERENCES.md §7 lines 229-242`, year-end gate §13.

## Late-career resource planning (new)

Review remaining objectives and available resources before the finale:

```
LATE-CAREER REVIEW

Remaining GP:     190 (Senior period)
Coins:            150 (no income after finale races)
Hammers held:     3/5  ✓ recommended
Megaphones:       2/5  ✓ Summer stock sufficient
```

## Shop

Display:

- item
- cost
- effect
- duration
- eligibility (locked until Debut, per `SCENARIO-PUBLISHER-REFERENCES.md` line 374)

**No recommendation field.** The shop rotation is not modelled (`config/scenarios.php` `trackblazer.shop`); displaying a "recommended purchase" would fabricate data. Community opinions are shown as separate, labelled tags only.

Example:

```text
Pro Shop                                coins 210   refresh in 3 turns

Master Cleat Hammer    40   +35% race bonus, 1 turn     held 1/5
Empowering Megaphone   70   +60% training, 2 turns      held 2/5
Reset Whistle          20   shuffle support cards       held 0/5
Good-Luck Charm        40   0% failure, 1 turn          held 1/5
Vita 65                75   +65 energy                  held 0/5

Community tags (uma.guide): Must Buy / Skip
```

---

# 20b. SCREEN-016b — Scenario Panel: Grand Concert (our_grand_concert)

## Purpose

Surface verified Grand Concert state. The evidence hierarchy (`docs/Our-Grand-Concert-plan.md`) determines what is renderable:

- **Available (Confirmed):** Performance type (Dance, Passion, Vocals, Visuals, Composure) and signed delta as turn observations.
- **Observed but undocumented:** nothing beyond the above.
- **Documented as unverified:** starting values, caps, Lesson costs, Song titles, Live Bonus percentages, Hype, Promo Concert timing, outcome words beyond "Great Success".
- **Inferred/community:** none rendered.

## Components

- Performance strip (type selector + signed delta input)
- Finale presence indicator (catalogue row-driven, not config-key-driven)
- Blocked-mechanic placeholders (Lessons, Songs, Live Bonuses, Promo Concerts) with the evidence gate and reopen condition

## States

- **Pre-debut:** Performance input disabled; "Waiting for career start."
- **Active:** Performance type selector (5 options, no default) + signed delta field.
- **Recording error:** zero, decimal, or invalid terminology rejected (`GrandConcertPanelTest.php:93,103`).

## What is explicitly not built

- Starting values, current totals, caps, acquisition formulas, Lesson costs, Song titles, Hype, Live Bonus percentages, Promo Concert scheduling, or any recommendation logic. The panel states the evidence boundary and links to `SCREEN_SPEC.md` §7-19 through §7-23 for the reopen conditions.

Source: `docs/Our-Grand-Concert-plan.md` Slices 1–18, `docs/UMAMUSUME_REFERENCE.md §2.9`.

---

# 21. SCREEN-017 — Scenario Race Planner

## Purpose

Provide scenario-aware race planning.

## Features

- upcoming races
- mandatory races
- optional races
- Rival races
- reward comparison
- target alignment
- expected risk

## Recommendation

```text
RECOMMENDED

Race — Kyoto 1600m (G2 Mile, Turf)

READINESS: GOOD (Speed meets target, Stamina at floor)

Benefits:
  + 80 Grade Points
  + 100 Shop Coins
  + Route progress (2 of 3 on Spring Champion line)

Trade-off:
  -2 fatigue risk on this chain
  Training Speed now would gain less than the checkpoint risk

Missing data: opponent strength, exact placement modifiers
```

**No numeric win probability.** Race prediction is deferred on the `ADR-0016` data blocker. The recommendation shows readiness bands, known benefits, and the opportunity cost — never a fabricated percentage.

---

# 22. SCREEN-018 — Career Timeline

## Purpose

Provide a complete historical view of the run.

## Timeline events

- training
- races
- events
- inheritance
- scenario actions
- purchases
- important decisions

Each event records:

- before state
- action
- expected consequence (where supportable, with provenance label)
- actual result (when entered by the user)
- after state
- advisor confidence at time of decision

Example:

```text
Turn 37

TRAIN SPEED

Before:
Speed 842
Energy 72

Expected (Calculated):
+6 Speed, +2 Power, +5 SP
Facility: 8/12 to Lv 3

Action: Train Speed  ✓

Actual:
Speed 904
Energy 51

After:
Speed 904
Energy 51
Facility: 9/12 to Lv 3

Advisor at turn 37: recommended Speed training (verified, repetition level)
```

---

# 23. SCREEN-019 — Career Result

## Purpose

Summarize the completed career.

## Sections

### Final build

- stats
- skills
- aptitudes

### Race history

- races
- wins
- losses
- G1 victories

### Scenario result

- objectives
- scenario score
- scenario rewards

### Build quality

- target completion
- skill coverage
- inheritance quality

---

# 24. SCREEN-020 — Save Veteran

## Purpose

Convert the finished trainee into a reusable Veteran record.

## Fields

- Veteran name
- tags
- notes
- parent suitability
- intended use

## Suggested tags

- Speed
- Stamina
- Power
- Guts
- Wit
- Sprint
- Mile
- Medium
- Long
- Dirt
- Turf
- Front Runner
- Pace Chaser
- Late Surger
- End Closer
- Skill
- Race
- Scenario

---

# 25. SCREEN-021 — Veteran Library

## Purpose

Manage completed veterans.

## Features

- search
- filtering
- sorting
- comparison
- favorite
- archive
- delete

## Sort options

- Spark quality
- aptitude
- skill coverage
- race history
- completion date
- scenario
- overall usefulness

---

# 26. SCREEN-022 — Veteran Comparison

Compare up to four veterans.

Columns:

- stats
- Sparks
- skills
- race history
- aptitude
- scenario factor
- inheritance usefulness

---

# 27. SCREEN-023 — Database

## Purpose

Browse game data.

## Sections

- Trainees
- Support Cards
- Skills
- Races
- Events
- Scenarios
- Shop Items
- Sparks

Database screens are informational and should not overwhelm the main career workflow.

---

# 28. SCREEN-024 — Settings

## Categories

### General

- theme
- language
- units
- default scenario

### Recommendation

- recommendation aggressiveness
- risk tolerance
- stat target defaults
- race risk thresholds

### Data

- import
- export
- backup
- restore
- reset

### Game Version

Display:

```text
Global Ruleset
Version: YYYY.MM
```text

Game data and scenario rules must be versioned.

---

# 29. Global Interaction Rules

## Recommendation hierarchy

The recommendation engine evaluates:

1. Survival / avoiding failure
2. Mandatory objectives
3. Scenario objectives
4. Build targets
5. Support bonding
6. Scenario resources
7. Long-term optimization

## Recommendations must be explainable

Never show:

> "Best action: Train Speed."

without a reason.

Instead:

> Train Speed because Speed is 94 below target, three supports gain bond,
> and there is no mandatory race before the next recovery opportunity.

---

# 30. Recommendation States

Every recommendation has:

- Recommended action
- Confidence
- Reasons
- Alternatives
- Risks

Example:

```text
BEST ACTION
Train Speed

Confidence: HIGH

Reasons:
+ Target deficit
+ Strong support concentration
+ Low failure chance
+ No immediate race requirement

Alternative:
Train Wit

Risk:
Delays Speed target by approximately one turn.
```text

---

# 31. Data Integrity

The application must distinguish:

CONFIRMED

- manually entered state
- database values

CALCULATED

- derived stat
- recommendation
- projected outcome

PROBABILISTIC

- estimated race result
- RNG outcome

UNKNOWN

- information not yet entered

---

# 32. Career State Persistence

Every action should be persisted locally.

A CareerRun contains:

- scenario
- trainee
- target
- inheritance
- support deck
- current state
- action history
- scenario state
- final result

The user must be able to:

- resume
- inspect history
- undo the latest action
- manually correct state

---

# 33. Responsive Behavior

## Desktop

Primary target.

Use:

```text
Navigation
+
Timeline
+
Main Decision Area
+
Advisor
```text

## Tablet

Collapse:

- Timeline into drawer
- Advisor into collapsible panel

## Mobile

Use a single-column workflow:

```text
Header
Current State
Recommendation
Action
Scenario
Timeline
```text

Never require horizontal scrolling for primary decisions.

---

# 34. Accessibility

Requirements:

- keyboard navigation
- visible focus
- minimum touch target 44px
- semantic headings
- accessible labels
- do not rely on color alone
- support reduced motion
- sufficient contrast
- numerical values paired with visual indicators

**Image slots** are governed by `design-2.0` §42 (alt text, reserved-box contrast, label-in-name) and §45a (per-screen placement, geometry, click action). WCAG 2.2 AA conformance for slot-bearing screens is verified by the axe pass in `docs/proposals/frontend-development-plan.md` §12, *per screen*; the law-by-law review of "recognition rather than recall" and "minimal design" sits in that plan's §13.

---

# 35. Sourced image slots

Per-screen placement is `design-2.0` §45a; the Blade parity rule is `DESIGN.md` §4.7. A cell that ships no `<img>` today stays a text-only row. The four absence states (`never mirrored`, `gone upstream`, `unreadable on disk`, `not yet mirrored`) render identically — there is no broken frame, no grey box, no placeholder glyph (R-31). Skill icons are deferred until `skills.iconid` exists as a column (a migration of its own; `ADR-0021` Verification records the finding). Reduced motion is inherited from `design-2.0` §43 and from `frontend-development-plan.md` §12.2: a slot's reveal is no animation, so a fixed focal-length layout is what a screen reader and a sighted Trainer both experience.

---

# 36. Error States

The UI must clearly handle:

- incomplete career state
- missing database data
- invalid support deck
- invalid inheritance configuration
- stale game ruleset
- corrupted local data
- unavailable recommendation

Example:

> Recommendation unavailable because Energy and Mood have not been entered.

Do not silently guess.

---

# 37. Screen States for Changed Screens

For each screen that changed in this pass, the following states must be defined and handled:

- **Initial / empty** — no run data loaded.
- **Normal populated** — full run state with verified data.
- **Partial or unknown data** — some fields missing, stale, or `N/A`.
- **Validation or recording error** — user entry fails validation or save fails.
- **Completed or no-longer-applicable** — objective achieved or superseded.
- **Recommendation unavailable** — insufficient rules or state to rank actions.

| Screen        | States asserted in browser tests                                                                 |
| ------------- | ----------------------------------------------------------------------------------------------- |
| SCREEN-009    | Empty run (no state), populated run with recommendation, recommendation unsupported, action recorded but result pending, action recorded with reconciliation |
| SCREEN-014    | Pre-debut (no fan gates active), gates active with partial progress, qualifier passed, pre-finale review, all gates completed |
| SCREEN-015    | No team data, partial roster, full roster with mixed ranks, tournament locked, team rank `N/A` (no recorded letter) |
| SCREEN-016    | Pre-debut (shop locked), shop open with partial coin balance, GP period completed, finale reached (coin income ends), schedule risk flagged |

**Keyboard and narrow-screen behavior:** the primary recommendation must remain visible above secondary charts and logs at 320px width. The Next Decision component must be reachable via keyboard in a single `Tab` from the stat bands. Colour is never the sole status indicator — text and icon must carry the same meaning.
