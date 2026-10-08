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
> (_Dated close-out 2026-10-08, documentation-sync pass: the Inertia + Vue rewrite this banner says "§1
> targets" landed through Phases A–E. The shipped surface and every screen's real shape are recorded in
> `SCREEN_SPEC.md` §3/§4 (`SCR-CAR-001`–`024`, `SCR-VET-001`–`004`), the visual decisions in `DESIGN.md`,
> and the slice-by-slice landings in `docs/proposals/frontend-development-plan.md` §4–§9, which this file's
> screen inventory was deliberately built as the union-with-`screen-spec-2.0.md` input for. Two of this
> document's Grand Concert premises are superseded by the tree and recorded in the plan's E6 close-out and
> `SCREEN_SPEC.md` SCR-CAR-024: `docs/scenarios/07-grand-concert.md` is a sourced guide rather than a stub,
> and the config key that drives the PARTIALLY DOCUMENTED badge is `partially_documented` (the owner's
> ruling of 2026-10-07), not `documented => false`. This file stays the reference target its banner calls
> it; the close-outs it points to are the record of what shipped._
> to it rather than re-deriving per-scenario logic in components.

---

## Product direction corrections (added 2026-10-05)

A new product/UX strategy document (owner-supplied, 2026-10-05) corrects several assumptions in this brief.
This section records those corrections without rewriting the brief itself. The brief stays verbatim; the
corrections are additive notes so a reader can tell what stands and what must change before implementation.

**Scope.** The corrected direction treats the app as a **"Trainer's planning desk"** rather than a "career
optimizer." It separates **Preparation mode** (answering "What am I going to build?") from **Active Career
mode** (answering "Given what actually happened, what should I do next?"). The application never pretends its
simulated state is the game's authoritative state.

**Key corrections:**

| Brief section   | Original assumption                              | Corrected direction                                                                                                                                                                                                                                                                                                   |
| --------------- | ------------------------------------------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| §1–2            | Three scenarios (URA, Unity Cup, Trackblazer)    | **Four Global scenarios**: URA Finale, Unity Cup, Trackblazer, Brighter Together: Our Grand Concert (mechanics ❌ UNVERIFIED, baseline strip only)                                                                                                                                                                    |
| §15             | Generic "scenario difficulty" rating             | Remove star ratings; instead show **what each scenario optimizes** (primary/secondary/training complexity)                                                                                                                                                                                                            |
| §19–20          | Sparks shown as star counts                      | Add **probability model**: ★ star ratings are roll probabilities, not guarantees. Display `Potential payout` + `Estimated roll ~X%`. **Factor yield clarified 2026-10-05**: exactly 1 Blue + 1 Pink per run, at most 1 Green (requires 3★ parent), White sparks unbounded — model as variable yield, not a slot cap   |
| §21             | Affinity as a named value                        | Make affinity a **first-class planning concept**: six-node ancestry graph with visual compatibility calculation                                                                                                                                                                                                       |
| §22             | Two-parent selection                             | Upgrade to **six-node Legacy configuration** (Parent A/B + four grandparents); optimize entire ancestry, not just parents                                                                                                                                                                                             |
| §23             | Five support types + borrowed slot               | **Seven support types**: Speed, Stamina, Power, Guts, Wit, **Pal**, **Group**. Scenario Link is **derived** from scenario+character relationship, not stored on card                                                                                                                                                  |
| §26–31          | Scenario panels as static modules                | Each panel must reflect scenario-specific currencies/objectives; Grand Concert initially limited to basic tracking until mechanics verified                                                                                                                                                                           |
| §36             | Simple Veteran save                              | **Veteran Creation screen** with factor analysis, legacy value assessment, tagging, and "Optimize Next Career" loop                                                                                                                                                                                                   |
| §44             | LLM-style recommendation                         | **Deterministic rules engine first** (hard constraints → optimization), explanatory second. Never invent recommendations via AI                                                                                                                                                                                       |
| §45             | Three-state model (observed/derived/predicted)   | **Four-state model**: Observed / Calculated / Predicted / RNG. Visually distinct rendering                                                                                                                                                                                                                            |
| §48             | Versioning UI                                    | Make versioning **mandatory**: every career run preserves its ruleset snapshot (`Global 2026-07-01 rebalance`)                                                                                                                                                                                                        |
| Appendix        | Five-component hierarchy                         | Expand to include **Legacy Lab (six-node)**, **Support Deck (seven types)**, **Career State Machine**                                                                                                                                                                                                                 |

**New domain objects introduced:**

- **Career Plan**: purpose, scenario, trainee, race profile, stat targets, aptitude targets, running style, skill targets, legacy requirements, support requirements, risk tolerance
- **Career State Machine**: explicit state (`current_year`, `current_half`, `current_turn`, `energy`, `mood`, `stats`, `skill_points`, `fans`, `support_bonds`, `races`, `events`, `goals`, `scenario_state`, `inheritance_state`, `action_history`)
- **GameRule**: key, scenario_id, server, version, value, source, source_type, confidence, verified_at, notes
- **Event Model**: source (Support/Character/Scenario/Random), choices, known outcomes, current career state, expected effect, recommendation

**Removed concepts:**

- "Trainer Abilities" system (does not exist on client)
- "Friendship radius" term (not an established game term)
- Generic five-type support model (must be seven types)
- Borrowed-card structural assumption (deck is six slots, ownership is OWNED/RENTED flag)

**Implementation priority shift:**

The corrected direction changes development order:

1. **Phase 0** — Data foundation (terminology, versions, cards, races, skills, sparks, veterans, sources, confidence)
2. **Phase 1** — Career Planner (scenario, trainee, plan, race profile, targets)
3. **Phase 2** — Legacy Lab (veteran library, six-node ancestry, sparks, affinity, probabilities, search)
4. **Phase 3** — Support Deck (six slots, seven types, limit break, effects, friendship, hints, scenario link)
5. **Phase 4** — Active Career State (turn, energy, mood, stats, SP, fans, goals, history, bonds, events)
6. **Phase 5** — Deterministic Advisor (hard constraints, scenario rules, training comparison, race requirements, deficits, risk)
7. **Phase 6** — Scenario Modules (URA → Unity Cup → Trackblazer → Grand Concert)

**Race simulator explicitly deferred:** Do not build full race simulator in MVP. Use conservative readiness bands (Excellent/Good/Borderline/Poor) rather than fake precision ("Win probability: 84%").

**Screenshot-assisted entry noted as future feature:** Import screenshot → OCR detection → user confirmation → store as `USER_CONFIRMED_SCREENSHOT`.

**Undo strongly recommended:** Because this is local planning (not game manipulation), undo last recorded action is safe and valuable.

**Data provenance layer:** Every important mechanic needs provenance/version/confidence. Raw game knowledge → normalized data → rule engine → player-facing model. UI always uses Global labels.

**Research confidence indicator:** Subtle dashboard badge showing data status (● Current / ⚠ Snapshot / ? Unverified). Tells user "this isn't connected to Cygames."

---

## Knowledge grounding (added 2026-10-05)

The brief below cites external sources (GameTora, uma.guide). This section reconciles each load-bearing
fact against the repository's own corpus, so a reader can tell what is sourced from what. It does not
edit the brief; the brief stays verbatim as the design target.

**Terminology.** The brief says "July 2026 rebalance". The corpus's canonical term is the
**2026-07-01 Global rework** (`docs/UMAMUSUME_REFERENCE.md` L1016/L1028/L1137;
`docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` L544 "July 1, 2026 Update — What Changed").
Where the two docs in this directory say "July 2026", read "2026-07-01".

**Provenance of the brief's mechanics claims.**

| Brief section                  | Claim                                                                             | Repository authority                                                                                       | Verdict                                                                                                                                                                                                                                        |
| ------------------------------ | --------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| §19–20 Sparks                  | Blue=Stats, Pink=Aptitude, Green=Unique, White=Skills/races/scenario              | `UMAMUSUME_REFERENCE.md` §1.5 L604–611                                                                     | Corroborated; **factor-slot count clarified 2026-10-05**: exactly 1 Blue + 1 Pink per run, at most 1 Green (requires 3★ parent), White sparks roll independently per category with no stated limit — model as variable yield, not a slot cap   |
| §21 Affinity                   | Affinity is a named compatibility value                                           | `UMAMUSUME_REFERENCE.md` §1.5 L645                                                                         | Corroborated                                                                                                                                                                                                                                   |
| §22 Parent tree                | Two parents + four grandparents                                                   | `UMAMUSUME_REFERENCE.md` §1.5 L637                                                                         | Corroborated                                                                                                                                                                                                                                   |
| §23 Support cards              | Many interacting bonuses and event effects                                        | `UMAMUSUME_REFERENCE.md` §1.4 L423–585; `docs/research-scratch/SUPPORT-CARDS.md`                           | Corroborated                                                                                                                                                                                                                                   |
| §26–31 Scenario languages      | URA/Happy Meek, Unity team+Spirit, Trackblazer Grade Points+shop, Grand Concert   | `config/scenarios.php`; `docs/scenarios/01`–`03`, `07`; `SCENARIO-PUBLISHER-REFERENCES.md` L68/L102/L702   | Corroborated, **except** Grand Concert (below)                                                                                                                                                                                                 |
| §15/§25 Training cards         | "+62 Speed / +25 Power", "Failure 2%"                                             | —                                                                                                          | **Not sourced.** `UMAMUSUME_REFERENCE.md` §1.1.1 L108–114 is marked ⚠️ STALE (GameWith 2023-02-25); no published per-training yield. Excluded by the advisor spec (`PROCESS-PLANS.md` `## trainer-advisor.md` §1, §5).                         |
| §26/§49 Win probability        | "Estimated win chance: 82%"                                                       | —                                                                                                          | **Not sourced.** Race prediction deferred on the `ADR-0016` data blocker (`ADR-0020` §4). Use conservative readiness bands (Excellent/Good/Borderline/Poor) rather than fake precision.                                                        |
| §44 Expert mode                | inheritance probability, race probability                                         | —                                                                                                          | **Deferred/banned.** Inheritance computation banned (`ADR-0020` §3); race probability per above.                                                                                                                                               |
| §48 Versioning UI              | "Ruleset: 2026.07"                                                                | `HandleInertiaRequests` shares `app.ruleset`                                                               | **Real but currently `null`** — no ruleset string is sourced. Render `N/A`, never invent a version. Grand Concert caps corroborated 2026-10-05 (two independent sources): 1600/1300/1300/1500/1300 for Speed/Stamina/Power/Guts/Wit.           |
| §19/§20 Sparks (star counts)   | ★ star ratings                                                                    | `UMAMUSUME_REFERENCE.md` §1.5 L621–627 (star-roll odds)                                                    | Corroborated as a mechanic; factor-slot count is ❌ UNVERIFIED (L641).                                                                                                                                                                         |

**Grand Concert.** The brief's §31 and its appendix treat Grand Concert as a fourth first-class scenario.
The repo agrees it is the fourth Global scenario, live 2026-07-22 and permanently selectable
(`config/scenarios.php` `our_grand_concert`, `live_on_global => '2026-07-22'`), **but its mechanics are
not held**: `docs/scenarios/07-grand-concert.md` is a known-gap stub and `UMAMUSUME_REFERENCE.md`
§2.2.4 L910–929 marks the mechanics ❌ UNVERIFIED with extraction suspended. `config/scenarios.php` encodes
this as `'documented' => false` and renders the baseline strip with every panel off (D-241, gate G-41).
The SPA must bind Grand Concert to that entry, not to the brief's fuller panel.

**Committed inventory, not a target.** The brief's §47 component hierarchy and the appendix file tree are
aspirational names. The components that exist today are the 24 committed Blade components listed in
`DESIGN.md` §3 (L235–279), all token-only. The rewrite ports those, then adds new ones; it does not
rename the library to match the brief.

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
```text

The application never removes player agency.

---

# 3. Visual Personality

Keywords:

- clean
- optimistic
- tactical
- organized
- friendly
- precise
- game-adjacent
- modern

Avoid:

- overly corporate dashboards
- dark hacker aesthetics
- excessive neon
- excessive anime decoration
- dense spreadsheet layouts
- unnecessary gamification

---

# 4. Visual Hierarchy

The UI has four levels.

## Level 1 — Critical

Things requiring immediate attention.

Examples:

- mandatory race
- imminent failure
- critical energy state
- scenario deadline
- recommended action

Visual treatment:

- strong emphasis
- clear icon
- prominent placement

---

## Level 2 — Important

Examples:

- target deficit
- support bond
- inheritance opportunity
- scenario resource

Visual treatment:

- card
- medium emphasis

---

## Level 3 — Informational

Examples:

- race history
- database values
- secondary projections

Visual treatment:

- muted text
- secondary cards

---

## Level 4 — Metadata

Examples:

- data source
- ruleset version
- timestamps

Visual treatment:

- smallest text
- low contrast but still accessible

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
```text

Do not use color as the sole indicator.

Example:

```text
✓ Safe
⚠ Risk
✕ Failure
```text

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
```text

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
```text

---

# 7. Typography

Typography must prioritize numerical readability.

## Font hierarchy

### Display

Used for:

- page titles
- career result
- major scenario titles

### Heading

Used for:

- section titles
- card headings

### Body

Used for:

- descriptions
- recommendations
- explanations

### Numeric

Used for:

- stats
- percentages
- currencies
- turn counters
- projections

Numerical information should use tabular numerals where available.

Example:

```text
Speed       1,204
Stamina       782
Power       1,091
```text

Numbers should visually align.

---

# 8. Spacing

Use a consistent spacing scale.

Recommended base unit:

```text
4px
```text

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
```text

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
```text

Avoid excessively rounded UI.

The application should feel like a tool rather than a toy.

---

# 10. Shadows

Use shadows sparingly.

Default cards should primarily rely on:

- background contrast
- borders
- spacing

Use shadows for:

- dialogs
- floating panels
- important recommendations
- menus

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
```text

Sidebar width:

```text
240–280px
```text

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
```text

Bad:

A card containing:

- training
- shop
- race
- inheritance
- support deck

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
```text

## Secondary

Used for alternatives.

```text
[ Edit Deck ]
```text

## Tertiary

Used for low-priority actions.

```text
View Details
```text

## Destructive

Used for:

- deleting veteran
- deleting career
- resetting data

Must require confirmation.

---

# 15. Stats

Stats should always be represented in two ways:

```text
Speed
1,024 / 1,200
██████████████░░
```text

This provides:

- exact numerical information
- visual progress

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
```text

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
```text

Badge hierarchy:

```text
S / A    Strong
B / C    Neutral
D / E    Weak
F / G    Very weak
```text

Do not communicate aptitude solely through color.

---

# 18. Sparks

Sparks have distinct visual identities.

```text
Blue      → Stats
Pink      → Aptitude
Green     → Unique
White     → Skills / races / factors
```text

Display star count prominently.

```text
Speed
★★★
```text

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
```text

The recommendation must always expose its reasoning.

---

# 20. Confidence

Use:

```text
HIGH
MEDIUM
LOW
```text

Do not display false precision such as:

```text
Confidence: 97.31%
```text

unless the underlying model actually supports that precision.

---

# 21. Risk Indicators

Three primary states:

```text
LOW RISK
MEDIUM RISK
HIGH RISK
```text

Optional fourth:

```text
CRITICAL
```text

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
```text

Current position:

```text
◉
```text

Completed:

```text
●
```text

Upcoming:

```text
○
```text

Missed/failed:

```text
×
```text

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
```text

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
```text

The recommended card gets an additional indicator:

```text
★ RECOMMENDED
```text

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
```text

Instead:

```text
Career Cockpit
      +
Scenario Panel
```text

This allows each scenario to introduce its own mechanics without
destroying the core layout.

---

# 26. URA Visual Language

URA should be the least visually complex scenario.

Focus:

- career goals
- Happy Meek
- finale progression

Avoid adding unnecessary permanent panels.

---

# 27. Unity Cup Visual Language

Unity Cup emphasizes:

- teams
- Spirit
- team rank
- training synergy

Use:

- team cards
- member rows
- Spirit meters
- burst indicators

The Team Panel should become a first-class UI element.

---

# 28. Trackblazer Visual Language

Trackblazer emphasizes:

- Grade Points
- Shop Coins
- races
- shop decisions
- Rival races

Use:

```text
Grade Points
Shop Coins
Race Calendar
Shop Inventory
Rival Alerts
```text

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
```text

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
```text

Because the application is local-first, loading should normally be minimal.

---

# 31. Modal Rules

Use modals only for:

- confirmation
- focused comparison
- detailed inspection
- destructive actions

Do not put primary workflows inside deep modal stacks.

---

# 32. Drawer Rules

Drawers are appropriate for:

- detailed database information
- race details
- support card details
- trainee details
- timeline entries

This allows the user to inspect information without losing the current context.

---

# 33. Tooltips

Tooltips should explain terminology.

Examples:

```text
Race Bonus
?
```text

Hover:

> Increases the rewards obtained from participating in races.

Tooltips should supplement the UI rather than contain essential information.

---

# 34. Information Density

Default:

```text
Moderate density
```text

Expert mode:

```text
High density
```text

The application should eventually support a density preference.

Casual mode:

- larger cards
- fewer numbers
- more explanations

Expert mode:

- compact cards
- more projections
- more detailed modifiers

---

# 35. Progressive Disclosure

Do not expose every modifier immediately.

Default:

```text
Speed +62
3 Supports
2% Failure
```text

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
```text

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
```text

Undo should restore:

- stats
- energy
- mood
- SP
- support state
- scenario state
- timeline

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
```text

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
```text

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
```text

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
```text

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
```text

Do not attempt to shrink the desktop sidebar onto mobile.

---

# 42. Accessibility

Minimum requirements:

- WCAG-conscious contrast
- keyboard navigation
- visible focus states
- semantic buttons
- ARIA labels where required
- reduced-motion support
- text alternatives for icons
- no color-only state indicators

**Image slots** follow the same contract, with three additional clauses:

- **Alt text** (WCAG 1.1.1 Non-text Content). The client display name from `lang/en/uma.php` and nothing else, never a fabricated descriptor. Where the same name is already printed beside the slot, the image takes `alt=""` so a screen reader does not read the name twice; the slot is decorative in that position.
- **Reserved-box contrast** (WCAG 1.4.11 Non-text Contrast). An absent slot's placeholder outline carries a 3:1 border against its parent surface, so the empty-state geometry is visible even without the file.
- **Label in name** (WCAG 2.5.3). A clickable slot's `aria-label` matches the printed trainee or support name verbatim, so a screen reader finds the control by the same word the row already prints.

The trust-row for images is bounded: a mirrored file is **Confirmed**; the four absence states (`never mirrored`, `gone upstream`, `unreadable on disk`, `not yet mirrored`) collapse to a single **Unknown** visual — the same text-only row the page already renders — and **Estimated** never applies (no upscaled thumbnail, no fake preview). The UX laws the proposal's §1 calls for apply in three named ways here: **Nielsen heuristic 6** — recognition rather than recall — is the slot's only justification; **Nielsen heuristic 8 + R-31** — minimal design — cut a slot the row's label already carries; **Don Norman's signifier** — the slot indexes the row's identity rather than claiming more information than the row does. **Fitts's Law** keeps the slot as a passive cell next to the existing row link: the click target stays the row's `h-11` link, not the image. Geometry follows §45a below.

---

# 43. Animation

Animation should communicate state changes.

Good:

- progress bar transition
- card selection
- recommendation appearing
- timeline progression

Avoid:

- excessive bouncing
- constant decorative animations
- long transitions

Default transition duration:

```text
150–200ms
```text

Important state changes may use:

```text
250–300ms
```text

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
```text

Do not use emoji as the primary icon system in production.

---

# 45. Data Visualization

Charts should be used only when they improve understanding.

Good:

- stat progression
- target progress
- career timeline
- race performance
- veteran comparison

Avoid:

- decorative pie charts
- excessive radar charts
- charts where a number would be clearer

## 45a. Sourced image slots

`ADR-0021` (`docs/adr/0021-sourced-character-artwork.md`, 2026-10-05) authorizes a local artwork mirror fetched by id from an allowlisted asset host. The placement decision is the owner's per `PRD.md` OQ-6; when the answer is "yes":

| Screen                               | Slot kind                                                                | Click action                       | Geometry                                    |
| ------------------------------------ | ------------------------------------------------------------------------ | ---------------------------------- | ------------------------------------------- |
| Catalog index, trainee card header   | portrait (`card_portrait` 256)                                           | navigates to trainee detail        | fixed `size-12` leading cell                |
| Catalog index, costume-form row      | portrait (`card_portrait` 256)                                           | navigates to trainee detail        | fixed `size-10` in the row header           |
| Catalog detail, Identity             | portrait (`card_portrait` 256, 512 if mirrored)                          | no action                          | fixed `size-16` aligned to the name block   |
| Support-card index, card row         | thumbnail (`full/small`)                                                 | navigates to support-card detail   | fixed `size-12` leading cell                |
| Support-card detail, header          | thumbnail (`full/small`)                                                 | no action                          | fixed `size-16`                             |
| Run Create / Legacy Select row       | portrait + thumbnail                                                     | row's form select                  | fixed `size-10`                             |
| Skill rows                           | **deferred** — `skills.iconid` has no column (`ADR-0021` Verification)   | n/a                                | n/a                                         |

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
```text

Never force the user to compare two separate cards mentally.

---

# 47. Expert Mode

Future feature.

Expert mode exposes:

- modifier breakdown
- support calculations
- probability estimates
- training formulas
- scenario modifiers
- inheritance probability
- race assumptions

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
```text

If the database and career state use different versions:

```text
⚠ Ruleset mismatch

This career was created using an older
scenario ruleset.

[Review Changes]
```text

---

# 49. Trust Model

The application should be honest about uncertainty.

Use:

```text
Confirmed
Calculated
Estimated
Unknown
```text

Never represent an estimate as a fact.

Example:

```text
Estimated win chance: 82%
```text

not:

```text
Win chance: 82%
```text

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
```text

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
```text

The two flagship experiences are the **Career Cockpit** ("what should I do on this turn?") and the
**Legacy Lab** ("how do I start this run?"). Note the Legacy Lab's recommendation/optimization parts are
governance-held (see banner); the record-and-browse parts are the authorized subset.
