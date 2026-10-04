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
