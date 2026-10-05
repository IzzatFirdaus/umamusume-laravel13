# Uma Trainer Desk — Design System (2.0 design target)

> **Status: 2.0 design target — reference only, filed 2026-10-04. Not authorization.**
>
> Source: owner-supplied design brief (attachment, 2026-10-04), reproduced verbatim below the rule line.
> This is the visual/interaction target for the Inertia + Vue rewrite (`ADR-0020` §1). The **shipped**
> Blade visual system is owned by `DESIGN.md`; where this target and `DESIGN.md` conflict about what ships
> today, `DESIGN.md` wins until the SPA slice lands. This document's philosophy — the trust model
> (Confirmed/Calculated/Estimated/Unknown), explainability, no false precision, accessibility — is
> consistent with `ADR-0001` and the Floor and is the intended carry-across.
>
> **Governance hold:** the §49 "Estimated win chance: 82%" example is race prediction — deferred on the
> `ADR-0016` data blocker (`ADR-0020` §4). It is kept verbatim as design intent; it is not authorization.
> The trust-model *rule* the example illustrates ("never represent an estimate as a fact") stands and is
> adopted.
>
> **Scenario architecture note:** the "Scenario Adapter" this document's appendix calls for already exists
> as the config-driven composition matrix (`config/scenarios.php`, gate G-33). The SPA rewrite binds panels
> to it rather than re-deriving per-scenario logic in components.

---

# Uma Trainer Desk — Design System

**Product:** Uma Trainer Desk
**Design direction:** Trainer Command Center
**Platform:** Local-first web application
**Primary audience:** Umamusume players who want more deliberate control over career planning

---

# 1. Design Philosophy

Uma Trainer Desk should feel like a sophisticated Trainer's personal
planning terminal.

It is NOT:

- a game clone
- a spreadsheet
- a wiki
- an automated bot
- an overwhelming statistics dashboard

It IS:

- a planning tool
- a decision assistant
- a career tracker
- a legacy optimizer
- a scenario-aware Trainer cockpit

The application should feel:

> Friendly enough for a casual player,
> precise enough for an optimization-focused player.

---

# 2. Core UX Principle

## State → Options → Recommendation → Explanation → Decision

Every important interaction should follow this structure.

```text
CURRENT STATE
      ↓
AVAILABLE OPTIONS
      ↓
BEST OPTION
      ↓
WHY?
      ↓
PLAYER DECIDES
```

The application never removes player agency.

---

# 3. Visual Personality

Keywords:

* clean
* optimistic
* tactical
* organized
* friendly
* precise
* game-adjacent
* modern

Avoid:

* overly corporate dashboards
* dark hacker aesthetics
* excessive neon
* excessive anime decoration
* dense spreadsheet layouts
* unnecessary gamification

---

# 4. Visual Hierarchy

The UI has four levels.

## Level 1 — Critical

Things requiring immediate attention.

Examples:

* mandatory race
* imminent failure
* critical energy state
* scenario deadline
* recommended action

Visual treatment:

* strong emphasis
* clear icon
* prominent placement

---

## Level 2 — Important

Examples:

* target deficit
* support bond
* inheritance opportunity
* scenario resource

Visual treatment:

* card
* medium emphasis

---

## Level 3 — Informational

Examples:

* race history
* database values
* secondary projections

Visual treatment:

* muted text
* secondary cards

---

## Level 4 — Metadata

Examples:

* data source
* ruleset version
* timestamps

Visual treatment:

* smallest text
* low contrast but still accessible

---

# 5. Color System

Colors communicate state rather than decoration.

## Semantic colors

```text
Success      → Green
Warning      → Amber
Danger       → Red
Information  → Blue
Special      → Purple
Exceptional  → Gold
Neutral      → Gray
```

Do not use color as the sole indicator.

Example:

```text
✓ Safe
⚠ Risk
✕ Failure
```

rather than relying only on colored text.

---

# 6. Brand Palette

The default palette should feel inspired by grass, racing tracks,
training facilities and the game's optimistic tone.

Use a restrained palette:

```text
Background
Warm off-white

Surface
White

Primary
Deep green

Primary soft
Pale green

Accent
Warm gold

Text
Deep navy

Muted
Slate gray
```

Exact values should be implemented as design tokens rather than hardcoded
throughout the application.

Example:

```css
--color-bg
--color-surface
--color-primary
--color-primary-soft
--color-accent
--color-text
--color-muted
--color-border
--color-success
--color-warning
--color-danger
--color-info
--color-special
```

---

# 7. Typography

Typography must prioritize numerical readability.

## Font hierarchy

### Display

Used for:

* page titles
* career result
* major scenario titles

### Heading

Used for:

* section titles
* card headings

### Body

Used for:

* descriptions
* recommendations
* explanations

### Numeric

Used for:

* stats
* percentages
* currencies
* turn counters
* projections

Numerical information should use tabular numerals where available.

Example:

```text
Speed       1,204
Stamina       782
Power       1,091
```

Numbers should visually align.

---

# 8. Spacing

Use a consistent spacing scale.

Recommended base unit:

```text
4px
```

Scale:

```text
4
8
12
16
20
24
32
40
48
64
```

Primary layout spacing should use 16px / 24px / 32px increments.

---

# 9. Border Radius

Use moderately rounded surfaces.

```text
Small controls     6px
Cards              10px
Large panels       14px
Dialogs            16px
Pills              999px
```

Avoid excessively rounded UI.

The application should feel like a tool rather than a toy.

---

# 10. Shadows

Use shadows sparingly.

Default cards should primarily rely on:

* background contrast
* borders
* spacing

Use shadows for:

* dialogs
* floating panels
* important recommendations
* menus

---

# 11. Application Shell

Desktop:

```text
┌───────────────────────────────────────────────────────────┐
│ Top Bar                                                   │
├───────────────┬───────────────────────────────────────────┤
│ Sidebar       │ Main Content                              │
│               │                                           │
│               │                                           │
└───────────────┴───────────────────────────────────────────┘
```

Sidebar width:

```text
240–280px
```

Main content should have a readable maximum width.

---

# 12. Cards

Cards are the fundamental grouping mechanism.

A card should represent one coherent concept.

Good:

```text
Speed Training
+62 Speed
+25 Power
3 Supports
```

Bad:

A card containing:

* training
* shop
* race
* inheritance
* support deck

Do not overload cards.

---

# 13. Primary Action

Every screen should have one obvious primary action.

Examples:

Scenario Selection:

> Select Scenario

Trainee Selection:

> Select Trainee

Legacy Lab:

> Confirm Inheritance

Deck Builder:

> Confirm Deck

Preflight:

> Start Career

Career Cockpit:

> Recommended Action

---

# 14. Buttons

## Primary

Used for the main action.

Example:

```text
[ Start Career ]
```

## Secondary

Used for alternatives.

```text
[ Edit Deck ]
```

## Tertiary

Used for low-priority actions.

```text
View Details
```

## Destructive

Used for:

* deleting veteran
* deleting career
* resetting data

Must require confirmation.

---

# 15. Stats

Stats should always be represented in two ways:

```text
Speed
1,024 / 1,200
██████████████░░
```

This provides:

* exact numerical information
* visual progress

Never use only progress bars.

---

# 16. Stat Colors

Stat identity may use subtle category accents.

```text
Speed
Stamina
Power
Guts
Wit
```

The colors should remain muted.

Do not create five highly saturated colors competing with each other.

---

# 17. Aptitude Display

Use compact badges.

```text
Turf       A
Medium     A
Long       B
Pace       A
```

Badge hierarchy:

```text
S / A    Strong
B / C    Neutral
D / E    Weak
F / G    Very weak
```

Do not communicate aptitude solely through color.

---

# 18. Sparks

Sparks have distinct visual identities.

```text
Blue      → Stats
Pink      → Aptitude
Green     → Unique
White     → Skills / races / factors
```

Display star count prominently.

```text
Speed
★★★
```

Use a consistent star component.

---

# 19. Recommendation Card

This is a signature component.

```text
┌───────────────────────────────────────────────┐
│ ★ BEST ACTION                                 │
│                                               │
│ TRAIN SPEED                                   │
│                                               │
│ Speed +62                                     │
│ Power +25                                     │
│                                               │
│ WHY                                           │
│ • Speed target is behind schedule             │
│ • 3 supports gain bond                        │
│ • Failure chance is low                       │
│                                               │
│ Confidence: HIGH                              │
│                                               │
│ [TRAIN SPEED]                                 │
│                                               │
│ Alternative: Train Wit                        │
└───────────────────────────────────────────────┘
```

The recommendation must always expose its reasoning.

---

# 20. Confidence

Use:

```text
HIGH
MEDIUM
LOW
```

Do not display false precision such as:

```text
Confidence: 97.31%
```

unless the underlying model actually supports that precision.

---

# 21. Risk Indicators

Three primary states:

```text
LOW RISK
MEDIUM RISK
HIGH RISK
```

Optional fourth:

```text
CRITICAL
```

Always pair with text.

---

# 22. Timeline

The career timeline is a major navigation component.

Visual structure:

```text
● Debut
│
● Classic
│
● Inheritance
│
● Goal Race
│
● Senior
│
● Finale
```

Current position:

```text
◉
```

Completed:

```text
●
```

Upcoming:

```text
○
```

Missed/failed:

```text
×
```

---

# 23. Career Cockpit Layout

The desktop cockpit uses three columns.

```text
Timeline
   20%

Current Decision
   50%

Trainer Advisor
   30%
```

The middle column always receives the greatest visual emphasis.

The player should never have to search for:

> "What should I do now?"

---

# 24. Decision Cards

Available actions should be visually comparable.

```text
┌────────────┐
│ SPEED      │
│ +62        │
│ LOW RISK   │
└────────────┘

┌────────────┐
│ POWER      │
│ +48        │
│ LOW RISK   │
└────────────┘

┌────────────┐
│ WIT        │
│ +35        │
│ SAFE       │
└────────────┘
```

The recommended card gets an additional indicator:

```text
★ RECOMMENDED
```

---

# 25. Scenario UI

Scenario mechanics should appear as modular panels.

The main Career Cockpit should not become:

```text
URA-specific
+
Unity-specific
+
Trackblazer-specific
+
Future scenario-specific
```

Instead:

```text
Career Cockpit
      +
Scenario Panel
```

This allows each scenario to introduce its own mechanics without
destroying the core layout.

---

# 26. URA Visual Language

URA should be the least visually complex scenario.

Focus:

* career goals
* Happy Meek
* finale progression

Avoid adding unnecessary permanent panels.

---

# 27. Unity Cup Visual Language

Unity Cup emphasizes:

* teams
* Spirit
* team rank
* training synergy

Use:

* team cards
* member rows
* Spirit meters
* burst indicators

The Team Panel should become a first-class UI element.

---

# 28. Trackblazer Visual Language

Trackblazer emphasizes:

* Grade Points
* Shop Coins
* races
* shop decisions
* Rival races

Use:

```text
Grade Points
Shop Coins
Race Calendar
Shop Inventory
Rival Alerts
```

The Shop should be quickly accessible from the Career Cockpit.

---

# 29. Empty States

Every empty state should explain:

1. What is missing
2. Why it matters
3. What the user can do

Example:

```text
No Veterans Yet

Complete your first career to create a Veteran
that can be used for future inheritance planning.

[Start Career]
```

---

# 30. Loading States

Prefer skeletons over full-screen spinners.

For example:

```text
┌──────────────────────────┐
│ █████████████████        │
│ █████████                │
│ ███████████████          │
└──────────────────────────┘
```

Because the application is local-first, loading should normally be minimal.

---

# 31. Modal Rules

Use modals only for:

* confirmation
* focused comparison
* detailed inspection
* destructive actions

Do not put primary workflows inside deep modal stacks.

---

# 32. Drawer Rules

Drawers are appropriate for:

* detailed database information
* race details
* support card details
* trainee details
* timeline entries

This allows the user to inspect information without losing the current context.

---

# 33. Tooltips

Tooltips should explain terminology.

Examples:

```text
Race Bonus
?
```

Hover:

> Increases the rewards obtained from participating in races.

Tooltips should supplement the UI rather than contain essential information.

---

# 34. Information Density

Default:

```text
Moderate density
```

Expert mode:

```text
High density
```

The application should eventually support a density preference.

Casual mode:

* larger cards
* fewer numbers
* more explanations

Expert mode:

* compact cards
* more projections
* more detailed modifiers

---

# 35. Progressive Disclosure

Do not expose every modifier immediately.

Default:

```text
Speed +62
3 Supports
2% Failure
```

Expand:

```text
Base training
+35

Support bonuses
+17

Friendship
+10

Scenario modifier
+0

Total
+62
```

This keeps the interface readable.

---

# 36. Explainability Rules

Whenever the system recommends something, the user must be able to answer:

> Why?

The explanation should reference actual state.

Good:

> Speed is 94 below target and three support cards benefit from this training.

Bad:

> This is mathematically optimal.

---

# 37. Undo

Career actions should support undo where practical.

Example:

```text
✓ Training recorded

[Undo]
```

Undo should restore:

* stats
* energy
* mood
* SP
* support state
* scenario state
* timeline

---

# 38. Manual Correction

The user must be able to correct a state.

Example:

```text
Speed
[904]

Energy
[51]

Mood
[Great]

[Save Correction]
```

This is essential because the application is an assistant rather than a direct
game integration.

---

# 39. Notification System

Use subtle notifications.

Examples:

```text
✓ Career state saved

⚠ Mandatory race approaching

★ New inheritance candidate found

⚠ Shop refresh in 1 turn
```

Notifications should never interrupt the user's current decision unnecessarily.

---

# 40. Responsive Design

## Desktop

Full three-column Career Cockpit.

## Tablet

Two columns:

```text
Main Decision
Advisor
```

Timeline becomes a horizontal strip.

## Mobile

One column:

```text
Career State
↓
Recommendation
↓
Actions
↓
Scenario
↓
Timeline
```

The recommendation remains above secondary information.

---

# 41. Mobile Navigation

Use a bottom navigation bar:

```text
Home
Career
Legacy
Deck
More
```

Do not attempt to shrink the desktop sidebar onto mobile.

---

# 42. Accessibility

Minimum requirements:

* WCAG-conscious contrast
* keyboard navigation
* visible focus states
* semantic buttons
* ARIA labels where required
* reduced-motion support
* text alternatives for icons
* no color-only state indicators

**Image slots** follow the same contract, with three additional clauses:

* **Alt text** (WCAG 1.1.1 Non-text Content). The client display name from `lang/en/uma.php` and nothing else, never a fabricated descriptor. Where the same name is already printed beside the slot, the image takes `alt=""` so a screen reader does not read the name twice; the slot is decorative in that position.
* **Reserved-box contrast** (WCAG 1.4.11 Non-text Contrast). An absent slot's placeholder outline carries a 3:1 border against its parent surface, so the empty-state geometry is visible even without the file.
* **Label in name** (WCAG 2.5.3). A clickable slot's `aria-label` matches the printed trainee or support name verbatim, so a screen reader finds the control by the same word the row already prints.

The trust-row for images is bounded: a mirrored file is **Confirmed**; the four absence states (`never mirrored`, `gone upstream`, `unreadable on disk`, `not yet mirrored`) collapse to a single **Unknown** visual — the same text-only row the page already renders — and **Estimated** never applies (no upscaled thumbnail, no fake preview). The UX laws the proposal's §1 calls for apply in three named ways here: **Nielsen heuristic 6** — recognition rather than recall — is the slot's only justification; **Nielsen heuristic 8 + R-31** — minimal design — cut a slot the row's label already carries; **Don Norman's signifier** — the slot indexes the row's identity rather than claiming more information than the row does. **Fitts's Law** keeps the slot as a passive cell next to the existing row link: the click target stays the row's `h-11` link, not the image. Geometry follows §45a below.

---

# 43. Animation

Animation should communicate state changes.

Good:

* progress bar transition
* card selection
* recommendation appearing
* timeline progression

Avoid:

* excessive bouncing
* constant decorative animations
* long transitions

Default transition duration:

```text
150–200ms
```

Important state changes may use:

```text
250–300ms
```

---

# 44. Icons

Icons should be simple and consistent.

Recommended conceptual icon mapping:

```text
Dashboard       Home
Career          Play / Flag
Legacy          DNA / Network
Support         Cards
Veterans        Trophy
Database        Book
Settings        Gear
Training        Dumbbell
Race            Flag
Energy          Battery
Skill Points    Spark
Shop            Store
Warning         Triangle
Recommendation Star
```

Do not use emoji as the primary icon system in production.

---

# 45. Data Visualization

Charts should be used only when they improve understanding.

Good:

* stat progression
* target progress
* career timeline
* race performance
* veteran comparison

Avoid:

* decorative pie charts
* excessive radar charts
* charts where a number would be clearer

## 45a. Sourced image slots

`ADR-0021` (`docs/adr/0021-sourced-character-artwork.md`, 2026-10-05) authorizes a local artwork mirror fetched by id from an allowlisted asset host. The placement decision is the owner's per `PRD.md` OQ-6; when the answer is "yes":

| Screen | Slot kind | Click action | Geometry |
|---|---|---|---|
| Catalog index, trainee card header | portrait (`card_portrait` 256) | navigates to trainee detail | fixed `size-12` leading cell |
| Catalog index, costume-form row | portrait (`card_portrait` 256) | navigates to trainee detail | fixed `size-10` in the row header |
| Catalog detail, Identity | portrait (`card_portrait` 256, 512 if mirrored) | no action | fixed `size-16` aligned to the name block |
| Support-card index, card row | thumbnail (`full/small`) | navigates to support-card detail | fixed `size-12` leading cell |
| Support-card detail, header | thumbnail (`full/small`) | no action | fixed `size-16` |
| Run Create / Legacy Select row | portrait + thumbnail | row's form select | fixed `size-10` |
| Skill rows | **deferred** — `skills.iconid` has no column (`ADR-0021` Verification) | n/a | n/a |

A click on any clickable slot terminates at the same destination as the row's existing link, satisfying WCAG 2.5.3 because `aria-label` is the row's printed name verbatim. Absence renders the text-only row that the screen ships today — no broken frame, no grey box, no placeholder glyph (R-31). At narrow viewports the slot cell is **omitted**, not reflowed (`WCAG 1.4.10 Reflow` holds without a second layout path). Reduced motion is inherited from §43; a slot either paints or it does not.

**Shipped-state note, 2026-10-05 (does not change the table above).** The table is the placement decision and it stands. What the Blade implementation found is narrower and belongs to the surfaces that exist rather than to the spec: the catalog index and the support-card pair placed their slots as written, and the **Run Create row cannot host one as built** — that screen's trainee picker is a native `<select>` whose `<option>` content model is text, plus a client-rendered combobox listbox, so there is no row to put a frame in, and `<img>` inside `<option>` is not rendered by the platform. Wiring the combobox listbox is a live option and is deferred, not refused. Two further gaps this document's rules imply but do not resolve: the catalog index resolves a trainee's portrait from her top-rarity form while the detail page resolves it from the active or first form, so a multi-form trainee can show two portraits across the two screens; and the two Blade components take a single `decorative` flag that blanks the image `alt` and the anchor `aria-label` together, which cannot express the combination this section's own alt clause (decorative image where the name prints beside it) and label-in-name clause (a named, clickable slot) both require at once. `DESIGN.md` §4.7 records the geometry values this section names, and records the second gap as known.

---

# 46. Comparison Design

Comparisons should align identical properties vertically.

Example:

```text
                 Veteran A     Veteran B
Speed Spark        ★★★           ★★
Medium             ★★★           ★★★
Skill A             ★★           ★★★
G1 wins             9             12
Affinity            High          Medium
```

Never force the user to compare two separate cards mentally.

---

# 47. Expert Mode

Future feature.

Expert mode exposes:

* modifier breakdown
* support calculations
* probability estimates
* training formulas
* scenario modifiers
* inheritance probability
* race assumptions

The default interface remains simpler.

---

# 48. Data Versioning UI

Because game mechanics change, the application must make its ruleset visible.

Example:

```text
Global
Ruleset: 2026.07

Scenario:
Unity Cup

Data updated:
2026-08-14
```

If the database and career state use different versions:

```text
⚠ Ruleset mismatch

This career was created using an older
scenario ruleset.

[Review Changes]
```

---

# 49. Trust Model

The application should be honest about uncertainty.

Use:

```text
Confirmed
Calculated
Estimated
Unknown
```

Never represent an estimate as a fact.

Example:

```text
Estimated win chance: 82%
```

not:

```text
Win chance: 82%
```

---

# 50. Core Design Rule

The entire application should optimize for one question:

> "What should I do next?"

Every major screen should make that answer obvious while allowing the player
to inspect the reasoning behind it.

The application should feel like:

```text
        GAME STATE
             │
             ▼
      ┌──────────────┐
      │ TRAINER DESK │
      └──────┬───────┘
             │
      ┌──────┴───────┐
      ▼              ▼
  ANALYSIS       SCENARIO
      │              │
      └──────┬───────┘
             ▼
       RECOMMENDATION
             │
             ▼
          PLAYER
             │
             ▼
          ACTION
             │
             ▼
       UPDATED STATE
```

The player remains the Trainer.

The application remains the assistant.

---

## Appendix — Frontend architecture (from the design brief)

The brief proposes this Inertia/Vue structure for the SPA rewrite. It is the *target* layout, not the
current `resources/js/` (which is TypeScript-only over Blade). The key architectural rule — the UI must
not know how a scenario works — is already satisfied server-side by `config/scenarios.php`; the Vue layer
binds to the resolved matrix rather than branching on scenario names.

```text
resources/js/
│
├── layouts/
│   ├── AppLayout.vue
│   ├── CareerLayout.vue
│   └── SetupLayout.vue
│
├── pages/
│   ├── Dashboard.vue
│   ├── Career/
│   │   ├── ScenarioSelect.vue
│   │   ├── TraineeSelect.vue
│   │   ├── BuildTarget.vue
│   │   ├── Preflight.vue
│   │   ├── Cockpit.vue
│   │   ├── Timeline.vue
│   │   └── Result.vue
│   ├── Legacy/
│   │   ├── Index.vue
│   │   ├── Builder.vue
│   │   └── Compare.vue
│   ├── Support/
│   │   ├── Index.vue
│   │   └── Builder.vue
│   ├── Veterans/
│   │   ├── Index.vue
│   │   └── Show.vue
│   └── Database/
│       ├── Trainees.vue
│       ├── Supports.vue
│       ├── Skills.vue
│       ├── Races.vue
│       └── Scenarios.vue
│
└── components/
    ├── app/
    │   ├── AppSidebar.vue
    │   ├── AppHeader.vue
    │   └── StatusBar.vue
    ├── career/
    │   ├── CareerHeader.vue
    │   ├── StatPanel.vue
    │   ├── StatProgress.vue
    │   ├── CareerTimeline.vue
    │   ├── TrainingCard.vue
    │   ├── RaceCard.vue
    │   ├── EventCard.vue
    │   ├── DecisionPanel.vue
    │   └── CareerAdvisor.vue
    ├── legacy/
    │   ├── VeteranCard.vue
    │   ├── LegacyTree.vue
    │   ├── SparkDisplay.vue
    │   ├── AffinityDisplay.vue
    │   └── LegacyRecommendation.vue
    ├── support/
    │   ├── SupportCard.vue
    │   ├── SupportSlot.vue
    │   ├── DeckAnalysis.vue
    │   └── DeckRecommendation.vue
    └── scenario/
        ├── ScenarioPanel.vue
        ├── UraPanel.vue
        ├── UnityCupPanel.vue
        └── TrackblazerPanel.vue
```

The two flagship experiences are the **Career Cockpit** ("what should I do on this turn?") and the
**Legacy Lab** ("how do I start this run?"). Note the Legacy Lab's recommendation/optimization parts are
governance-held (see banner); the record-and-browse parts are the authorized subset.
