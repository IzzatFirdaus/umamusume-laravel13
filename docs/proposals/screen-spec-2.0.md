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

---

## Product direction corrections (added 2026-10-05)

A new product/UX strategy document (owner-supplied, 2026-10-05) corrects several assumptions in this brief.
This section records those corrections without rewriting the brief itself. The brief stays verbatim; the
corrections are additive notes so a reader can tell what stands and what must change before implementation.

**Core UX model shift.** The application is reoriented from "career optimizer" to **"Trainer's planning desk."**
It separates **Preparation mode** ("What am I going to build?") from **Active Career mode** ("Given what actually
happened, what should I do next?"). The app never pretends its simulated state is authoritative game state.

**Screen-level corrections:**

| Screen | Original assumption | Corrected direction |
|---|---|---|
| SCREEN-002 (Scenario Selection) | Three scenarios with difficulty stars | **Four Global scenarios**; remove star ratings; show optimization focus (primary/secondary/training complexity); Grand Concert marked "PARTIALLY DOCUMENTED" |
| SCREEN-005 (Build Target) | Generic stat targets | Add **Career Plan object**: purpose, scenario, trainee, race profile, stat/aptitude/style/skill targets, legacy/support requirements, risk tolerance. Render as "Your target" not "The correct target" |
| SCREEN-006 (Legacy Lab) | Two-parent picker | Upgrade to **six-node ancestry planner** (Parent A/B + four grandparents). Show Spark probabilities (`~10% ★★★`), not guarantees. Distinguish Blue/Pink/Green/White/Scenario Sparks. Optimize entire ancestry configuration. **Factor yield clarified 2026-10-05**: exactly 1 Blue + 1 Pink per run, at most 1 Green (requires 3★ parent), White sparks unbounded — model as variable yield, not a slot cap |
| SCREEN-007 (Support Deck) | Five owned + one borrowed | **Six slots**, ownership flag (OWNED/RENTED). **Seven support types**: Speed, Stamina, Power, Guts, Wit, Pal, Group. Scenario Link derived from scenario+character, not stored on card. Granular deck analysis (training power, early run, race, safety, events, skills) |
| SCREEN-008 (Run Preflight) | Simple validation | Rename internally to **"Career Contract"**: scenario, trainee, six Legacy members, sparks analyzed, support deck, race profile, stat/skill/aptitude targets, risk profile. Warnings for low factor probability, missing aptitudes |
| SCREEN-009 (Career Cockpit) | Dashboard | Make it a **state machine**: explicit `CareerState` (year, half, turn, energy, mood, stats, SP, fans, bonds, races, events, goals, scenario_state, inheritance_state, action_history). Every action: BEFORE → USER ENTERS → EXPECTED → ACTUAL → UPDATED STATE → RECALCULATED |
| SCREEN-010 (Training Decision) | "+62 Speed / +25 Power" yields | Per-training yields are **unsourced** (§1.1.1 ⚠️ STALE). Render `N/A`. Keep decision card structure but remove specific yield numbers |
| SCREEN-011 (Race Decision) | Win probability percentage | Race prediction **deferred** (`ADR-0016`). Use conservative readiness bands (Excellent/Good/Borderline/Poor), not fake precision ("Win probability: 84%"). Model race as complex object (surface, distance, band, style, grade, venue, layout, corners, straights, elevation, weather, ground, season, time, fans, skill interactions, scenario effects) |
| SCREEN-012 (Event Decision) | Simple choice recording | Model event as: source (Support/Character/Scenario/Random), choices, known outcomes, current career state, expected effect, recommendation. If outcome incomplete: "⚠ Event outcome incomplete — choose manually" |
| SCREEN-013 (Inheritance Event) | Expected inheritance display | Show **probability, not guarantees**. Factor outlook: `Speed ★★★ Potential payout: +21 Estimated roll: ~10%`. NOT "guaranteed" |
| SCREEN-014–017 (Scenario Panels) | Static modules | Each panel genuinely different: URA (goals + Happy Meek), Unity Cup (**Team Cockpit** with team rank/spirit/bursts), Trackblazer (Grade Points + shop + rivals + Twinkle Star Climax), Grand Concert (basic tracker only, advanced advisor limited) |
| SCREEN-018 (Career Timeline) | Turn log | Record **decisions, not just turns**: BEFORE state, ACTION, EXPECTED result, ACTUAL result, RESULT, DECISION accepted/rejected, ADVISOR CONFIDENCE. Creates career audit trail |
| SCREEN-019 (Career Result) | Summary report | Upgrade to **Veteran Creation screen**: career result, aptitudes, skills, race history, sparks, legacy value (factor quality assessment), best use recommendation |
| SCREEN-020 (Save Veteran) | Simple save | Add **Factor Analysis** workflow: veteran review → factor analysis → legacy value → tag → save. Recommendation: "Excellent Medium parent. Best used for: Medium, Pace Chaser, Speed-oriented builds" |
| SCREEN-021 (Veteran Library) | List view | Three views: **Veterans** (all completed careers), **Factors** (searchable factor inventory), **Ancestry** (see where Veteran came from — six-node graph). Add "Find Parents for This Build" button |
| SCREEN-022 (Veteran Comparison) | Side-by-side stats | Add factor comparison, ancestry visualization, compatibility calculation. Search by factor requirements, aptitude requirements, skill requirements, compatibility, scenario requirements |
| New (not numbered) | — | Add **Race Planner sophistication**: course analysis (final corner, slope, backstretch, final straight, last-spurt conditions, position triggers). Don't hard-code distance ranges without resolving conflicts (1400m: Game8=Sprint vs export=Mile) |

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
```

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

| Brief ID | Screen | Repo ID | Route / source | State |
|---|---|---|---|---|
| SCREEN-001 | Dashboard | — (SPA `Dashboard.vue`) | `GET /` (`home`) | Partly built; Active-Career/Recent-Veterans are named absences |
| SCREEN-002 | Scenario Selection | New | `config/scenarios.php` | New; binds the four-scenario matrix |
| SCREEN-003 | Trainee Selection | `SCR-CAT-001` | `GET /umamusume` | Port |
| SCREEN-004 | Trainee Profile | `SCR-CAT-002` | `GET /umamusume/{slug}` | Port |
| SCREEN-005 | Build Target | New (`FR-F`) | `training_runs.build_target` | New; spec `PROCESS-PLANS.md` `## trainer-advisor.md` §2 |
| SCREEN-006 | Legacy Lab | New (`FR-G`, record-only) | Veteran library | New; optimization parts **held** (`ADR-0020` §3) |
| SCREEN-007 | Support Deck Builder | New | `runs.deck.sync`; `x-deck-editor` | New screen over an existing write path |
| SCREEN-008 | Run Preflight | New | composes 005–007 | New |
| SCREEN-009 | Career Cockpit | New (descends `SCR-RUN-003`) | `GET /training-runs/{run}` | New; the flagship screen |
| SCREEN-010 | Training Decision | New (`FR-F`) | advisor spec | New; per-training yields **held** |
| SCREEN-011 | Race Decision | New | `RaceCatalogSlot` | New; win-probability **held** (`ADR-0016`) |
| SCREEN-012 | Event Decision | New | `TurnEvent` + `TurnEvents\*Payload` | New over existing event recording |
| SCREEN-013 | Inheritance Event | New | — | New; **record-only** (`ADR-0020` §3) |
| SCREEN-014 | Scenario Panel: URA Finale | New | `ura_finale` | New |
| SCREEN-015 | Scenario Panel: Unity Cup | New | `unity_cup`; `x-spirit-burst-roster`, `x-team-rank-gauge`, `x-team-race-panel` | New over existing components |
| SCREEN-016 | Scenario Panel: Trackblazer | New | `trackblazer`; `x-shop-panel`, `x-grade-point-meter`, `x-epithet-checklist` | New; shop recommendation **held** (rotation not modelled) |
| SCREEN-017 | Scenario Race Planner | New | `RaceCatalogSlot` | New; win-probability **held** (`ADR-0016`) |
| SCREEN-018 | Career Timeline | New | `TurnEntry` / `TurnEvent` | New |
| SCREEN-019 | Career Result | New | `TrainingRun` | New |
| SCREEN-020 | Save Veteran | New (`FR-G`) | — | New |
| SCREEN-021 | Veteran Library | New (`FR-G`) | — | New |
| SCREEN-022 | Veteran Comparison | New (`FR-G`) | — | New |
| SCREEN-023 | Database | `SCR-CAT-001/002`, `SCR-SKL-001/002`, `SCR-SUP-001/002` | `GET /umamusume`, `/skills`, `/support-cards` | Port; races and scenarios are new |
| SCREEN-024 | Settings | `SCR-SYS-002` | `GET /preferences` | Ported to Vue 2026-10-04 |

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
```

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
```

## Global navigation

* Dashboard
* New Career
* Legacy Lab
* Support Decks
* Veterans
* Database
* Settings

When a career is active, a dedicated Career navigation becomes available.

---

# 4. SCREEN-001 — Dashboard

## Purpose

Landing screen for returning users.

## Primary goals

* resume active career
* start new career
* inspect recent veterans
* access legacy tools
* see application/data status

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
```

## Components

* ActiveCareerCard
* NewCareerButton
* RecentVeterans
* RecentBuilds
* DataStatus
* QuickActions

## Empty state

If no active career exists:

> No active career. Start a new training run.

---

# 5. SCREEN-002 — Scenario Selection

## Purpose

Choose the career scenario.

## Scenario cards

Each card must display:

* scenario name
* short description
* complexity
* primary mechanic
* recommended use
* availability
* ruleset version

## Current scenarios

### URA Finale

Primary mechanic:

* standard career progression
* character goals
* Happy Meek system

### Unity Cup

Primary mechanic:

* team development
* team races
* Spirit
* Spirit Bursts
* Extreme Spirit Bursts

### Trackblazer

Primary mechanic:

* Grade Points
* Shop Coins
* Pro Shop
* Rival races
* Twinkle Star Climax

## Future scenarios

Future scenarios must be represented through the same Scenario interface.

The UI must not assume that every scenario uses:

* the same objectives
* the same currencies
* the same finale
* the same training rules

---

# 6. SCREEN-003 — Trainee Selection

## Purpose

Select the trainee for the career.

## Features

* searchable roster
* sorting
* filtering
* trainee comparison
* detailed trainee profile

## Filters

* Surface
* Distance
* Running style
* Aptitude
* Growth rate
* Scenario suitability
* Unique skill
* Character

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
```

---

# 7. SCREEN-004 — Trainee Profile

## Purpose

Provide complete build-relevant information before selection.

## Sections

### Basic

* Name
* Rarity
* Version
* Growth rates

### Aptitudes

* Surface
* Distance
* Running style

### Skills

* Unique skill
* Starting skills
* Awakening skills
* Event skills

### Career goals

Display the trainee's expected career objectives.

### Build analysis

Show:

* ideal distances
* suitable running styles
* recommended stat distribution
* useful inheritance
* useful support types

---

# 8. SCREEN-005 — Build Target

## Purpose

Define what the player wants from the run.

## Target purpose

Options:

* Story Clear
* Competitive Build
* Champions Meeting
* Parent Farming
* Skill Farming
* General Training

## Race profile

* Surface
* Distance
* Running style

## Target stats

```text
Speed       1200
Stamina      700
Power       1000
Guts         400
Wit          900
```

## Skill priorities

Each skill may be marked:

* Required
* High priority
* Optional
* Ignore

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
```

## Candidate filters

* Blue Sparks
* Pink Sparks
* Green Sparks
* White Sparks
* Distance
* Surface
* Running style
* Skills
* Race history
* Scenario factor
* Affinity

## Parent slots

* Parent A
* Parent B

## Grandparent visualization

Show:

```text
Parent A
├── Grandparent A1
└── Grandparent A2

Parent B
├── Grandparent B1
└── Grandparent B2
```

## Compatibility analysis

Display:

* affinity
* expected inheritance
* useful Sparks
* missing Sparks
* race compatibility
* skill coverage

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

* five owned cards
* one borrowed card

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
```

## Filters

* Type
* Rarity
* Level
* Limit Break
* Skill
* Training bonus
* Race bonus
* Scenario compatibility

## Deck analysis

Must explain:

* strengths
* weaknesses
* skill coverage
* stat coverage
* scenario compatibility
* recommended replacement

---

# 11. SCREEN-008 — Run Preflight

## Purpose

Final validation before creating a career run.

## Sections

### Build

* trainee
* scenario
* target
* inheritance

### Support deck

* six cards
* deck analysis

### Target

* stats
* skills
* race profile

### Warnings

Examples:

* Missing distance aptitude
* Weak stamina plan
* Low skill coverage
* Poor support synergy
* Missing scenario requirement

## Actions

* Back
* Edit Legacy
* Edit Deck
* Edit Target
* Start Career

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
```

## Header

Show:

* scenario
* year
* month
* turn
* energy
* mood
* fans
* skill points
* scenario resources

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
```

Stat targets should appear as progress bars.

---

# 14. SCREEN-010 — Training Decision

## Purpose

Compare available training actions.

## Training card

```text
SPEED

+62 Speed
+25 Power
+8 SP

Support:
3

Bond:
+7 / +7 / +5

Failure:
2%

Target impact:
Speed target +8%
```

## Actions

* Train
* Inspect details

## Details

Display:

* expected gains
* energy cost
* failure probability
* support effects
* bond gains
* scenario effects
* target impact

---

# 15. SCREEN-011 — Race Decision

## Purpose

Select whether and where to race.

## Race cards

Display:

* race name
* grade
* distance
* surface
* running style
* expected reward
* fan gain
* skill point gain
* scenario reward
* estimated win probability

## Risk indicator

```text
LOW       < 10%
MEDIUM    10–30%
HIGH      > 30%
```

The exact thresholds should be configurable.

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
```

The user must be able to override the recommendation.

---

# 17. SCREEN-013 — Inheritance Event

## Purpose

Track inheritance milestones during the career.

## Display

* parent Sparks
* grandparent Sparks
* expected inheritance
* previous inheritance
* newly activated Sparks

## Timeline

```text
Career Start
    ✓

Classic April
    ○

Senior April
    ○
```

---

# 18. SCREEN-014 — Scenario Panel: URA Finale

## Purpose

Expose URA-specific information.

## Components

* character goals
* URA progression
* Happy Meek status
* finale preparation
* upcoming mandatory races

## Happy Meek

Show:

* current level
* duel availability
* potential reward
* final-race contribution

---

# 19. SCREEN-015 — Scenario Panel: Unity Cup

## Purpose

Manage scenario-specific team progression.

## Components

* team rank
* team member stats
* team composition
* team races
* Special Training
* Spirit
* Spirit Burst
* Extreme Spirit Burst

## Team panel

```text
Team Rank: A+

Speed     A
Stamina   B
Power     A
Guts      B
Wit       A
```

## Spirit panel

Display:

* current Spirit
* burst readiness
* recommended timing
* projected benefit

---

# 20. SCREEN-016 — Scenario Panel: Trackblazer

## Purpose

Manage Trackblazer-specific resources.

## Components

* Grade Points
* Shop Coins
* Pro Shop
* purchased items
* Rival races
* race schedule
* Twinkle Star Climax

## Shop

Display:

* item
* cost
* effect
* duration
* recommendation

Example:

```text
Recommended Purchase

Speed Scroll
Cost: 30 Coins

Reason:
Current Speed deficit is high and the next
shop refresh is unlikely to provide a better
stat conversion.
```

---

# 21. SCREEN-017 — Scenario Race Planner

## Purpose

Provide scenario-aware race planning.

## Features

* upcoming races
* mandatory races
* optional races
* Rival races
* reward comparison
* target alignment
* expected risk

## Recommendation

```text
Recommended

RACE — Kyoto 1600m

Win probability: 84%

Benefits:
+ Grade Points
+ Shop Coins
+ Skill Hint

No critical training deadline will be missed.
```

---

# 22. SCREEN-018 — Career Timeline

## Purpose

Provide a complete historical view of the run.

## Timeline events

* training
* races
* events
* inheritance
* scenario actions
* purchases
* important decisions

Each event records:

* before state
* action
* after state

Example:

```text
Turn 37

TRAIN SPEED

Before:
Speed 842
Energy 72

After:
Speed 904
Energy 51

Support Bond:
Kitasan +7
```

---

# 23. SCREEN-019 — Career Result

## Purpose

Summarize the completed career.

## Sections

### Final build

* stats
* skills
* aptitudes

### Race history

* races
* wins
* losses
* G1 victories

### Scenario result

* objectives
* scenario score
* scenario rewards

### Build quality

* target completion
* skill coverage
* inheritance quality

---

# 24. SCREEN-020 — Save Veteran

## Purpose

Convert the finished trainee into a reusable Veteran record.

## Fields

* Veteran name
* tags
* notes
* parent suitability
* intended use

## Suggested tags

* Speed
* Stamina
* Power
* Guts
* Wit
* Sprint
* Mile
* Medium
* Long
* Dirt
* Turf
* Front Runner
* Pace Chaser
* Late Surger
* End Closer
* Skill
* Race
* Scenario

---

# 25. SCREEN-021 — Veteran Library

## Purpose

Manage completed veterans.

## Features

* search
* filtering
* sorting
* comparison
* favorite
* archive
* delete

## Sort options

* Spark quality
* aptitude
* skill coverage
* race history
* completion date
* scenario
* overall usefulness

---

# 26. SCREEN-022 — Veteran Comparison

Compare up to four veterans.

Columns:

* stats
* Sparks
* skills
* race history
* aptitude
* scenario factor
* inheritance usefulness

---

# 27. SCREEN-023 — Database

## Purpose

Browse game data.

## Sections

* Trainees
* Support Cards
* Skills
* Races
* Events
* Scenarios
* Shop Items
* Sparks

Database screens are informational and should not overwhelm the main career workflow.

---

# 28. SCREEN-024 — Settings

## Categories

### General

* theme
* language
* units
* default scenario

### Recommendation

* recommendation aggressiveness
* risk tolerance
* stat target defaults
* race risk thresholds

### Data

* import
* export
* backup
* restore
* reset

### Game Version

Display:

```text
Global Ruleset
Version: YYYY.MM
```

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

## Recommendations must be explainable.

Never show:

> "Best action: Train Speed."

without a reason.

Instead:

> Train Speed because Speed is 94 below target, three supports gain bond,
> and there is no mandatory race before the next recovery opportunity.

---

# 30. Recommendation States

Every recommendation has:

* Recommended action
* Confidence
* Reasons
* Alternatives
* Risks

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
```

---

# 31. Data Integrity

The application must distinguish:

CONFIRMED

* manually entered state
* database values

CALCULATED

* derived stat
* recommendation
* projected outcome

PROBABILISTIC

* estimated race result
* RNG outcome

UNKNOWN

* information not yet entered

---

# 32. Career State Persistence

Every action should be persisted locally.

A CareerRun contains:

* scenario
* trainee
* target
* inheritance
* support deck
* current state
* action history
* scenario state
* final result

The user must be able to:

* resume
* inspect history
* undo the latest action
* manually correct state

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
```

## Tablet

Collapse:

* Timeline into drawer
* Advisor into collapsible panel

## Mobile

Use a single-column workflow:

```text
Header
Current State
Recommendation
Action
Scenario
Timeline
```

Never require horizontal scrolling for primary decisions.

---

# 34. Accessibility

Requirements:

* keyboard navigation
* visible focus
* minimum touch target 44px
* semantic headings
* accessible labels
* do not rely on color alone
* support reduced motion
* sufficient contrast
* numerical values paired with visual indicators

**Image slots** are governed by `design-2.0` §42 (alt text, reserved-box contrast, label-in-name) and §45a (per-screen placement, geometry, click action). WCAG 2.2 AA conformance for slot-bearing screens is verified by the axe pass in `docs/proposals/frontend-development-plan.md` §12, *per screen*; the law-by-law review of "recognition rather than recall" and "minimal design" sits in that plan's §13.

---

# 35. Sourced image slots

Per-screen placement is `design-2.0` §45a; the Blade parity rule is `DESIGN.md` §4.7. A cell that ships no `<img>` today stays a text-only row. The four absence states (`never mirrored`, `gone upstream`, `unreadable on disk`, `not yet mirrored`) render identically — there is no broken frame, no grey box, no placeholder glyph (R-31). Skill icons are deferred until `skills.iconid` exists as a column (a migration of its own; `ADR-0021` Verification records the finding). Reduced motion is inherited from `design-2.0` §43 and from `frontend-development-plan.md` §12.2: a slot's reveal is no animation, so a fixed focal-length layout is what a screen reader and a sighted Trainer both experience.

---

# 36. Error States

The UI must clearly handle:

* incomplete career state
* missing database data
* invalid support deck
* invalid inheritance configuration
* stale game ruleset
* corrupted local data
* unavailable recommendation

Example:

> Recommendation unavailable because Energy and Mood have not been entered.

Do not silently guess.
