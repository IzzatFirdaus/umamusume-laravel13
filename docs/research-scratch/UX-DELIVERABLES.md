# Umamusume: Pretty Derby — Merged UX Deliverables

## Provenance

This document consolidates the following source files verbatim (no summarization, no deduplication):

- `docs/UMAMUSUME PRETTY DERBY — COMPREHENSIVE UX DELIVERABLES.md` (2,144 lines)
- `docs/UX Behavior Specification - Umamusume Trainer Companion.md` (576 lines)
- `docs/Scenario-Specific User Flows & Frontend Specifications.md` (1,174 lines)

All three were added to the repository in `4ab5ada` and deleted by the consolidation in
`22e5135`, which left this merge as the only working-tree copy of their content. They are
recoverable from history: `git checkout 4ab5ada -- "<path>"`. Each was triaged on 2026-09-27
and never adopted, which is why the triage banners are reproduced in every part rather than
cleaned up. Headings were demoted one level per part; no source line was edited, and the
byte-level check that proves it is recorded in the commit that moved this file here.

This is the sixteenth master. Creating it required the owner's written authorization under
`INDEX.md` §File discipline, given on 2026-10-02. `## Source Documents` below is the
per-part mapping; this section is the file-level provenance the other masters use.

## Source Documents

- `UMAMUSUME PRETTY DERBY — COMPREHENSIVE UX DELIVERABLES.md` (Part 1: UMAMUSUME: PRETTY DERBY — COMPREHENSIVE UX DELIVERABLES)
- `UX Behavior Specification - Umamusume Trainer Companion.md` (Part 2: UX Behavior Specification: Umamusume Trainer Companion)
- `Scenario-Specific User Flows & Frontend Specifications.md` (Part 3: Scenario-Specific User Flows & Frontend Specifications)

The three source files remain on disk beside this document. This merge is a
reading aid that puts them in one place; it is not a replacement, and it resolves none
of the disagreements between them. Each source's own triage banner is carried below.

Erratum, 2026-10-02. The sentence above was true when it was written and is false now: the
consolidation in `22e5135` deleted all three sources, so this file holds their only surviving
copies in the working tree. What still holds is the rest of the paragraph: this is a reading
aid, not a replacement, and it resolves none of the disagreements. The sources' conflicting
figures are reproduced side by side in the parts below and are for a human to adjudicate.

---

## Part 1: UMAMUSUME: PRETTY DERBY — COMPREHENSIVE UX DELIVERABLES

> **NOT MERGED — TRIAGED 2026-09-27.** This file is retained for reference, not adopted as a source of
> values. Its numbers are governed by `docs/design-research/CONSTRAINTS.md` **D-283** (a design write-up
> supplies patterns, never values or copy), and the item-by-item audit is in
> `docs/design-research/MECHANICS-TRANSLATION-TRIAGE.md` **§7**. Read that before seeding anything here:
> roughly forty figures do not trace to any capture or dataset, this document's own vocabulary
> ("Factor", "Motivation", "parents") fails `make lore-code` despite its own terminology map at line 1098,
> and about a third of Part 1 specifies screens `PRD.md` §6 excludes. It also reproduces one error that
> was **ours**: the Trackblazer cap row at line 862 copies the transposed figure from
> `UMAMUSUME_REFERENCE.md:357`, since corrected. Do not treat agreement with this file as corroboration —
> it cites our own reference guide as its source, so it is a mirror (D-285).

### Document Purpose

This document translates every core and scenario game mechanic of *Umamusume: Pretty Derby* (Cygames) into three production-ready UX deliverables: a **Component Library Specification**, **User Flow Diagrams**, and a **Heuristic Evaluation**. It is written at maximum fidelity for consumption by an LLM agent tasked with generating design files, wireframes, or interactive prototypes. All numerical thresholds, terminology, and server-specific rules are drawn from the source-cited reference guide (compiled 2026-09-27) and the skill registry.

---

## PART 1: COMPONENT LIBRARY SPECIFICATION

### Naming Conventions

| Convention     | Rule                                                                                               |
| -------------- | -------------------------------------------------------------------------------------------------- |
| Component ID   | `UM-<SYSTEM>-<NUMBER>` (e.g., `UM-TRN-001`)                                                        |
| Server tag     | Every component carries `[JP]`, `[Global]`, or `[Both]`                                            |
| State naming   | `default`, `hover`, `active`, `disabled`, `error`, `success`, `loading`                            |
| Terminology    | Use `[Global]` client strings as primary labels; `[JP]` strings in parentheses where they differ   |

---

### 1.1 Training System Components

#### UM-TRN-001: Training Facility Tab

**Purpose:** Primary navigation element for selecting one of five training disciplines during a turn.

**Variants:** Speed, Stamina, Power, Guts, Wit

**Layout:**

- Horizontal tab bar, 5 slots, fixed order: Speed → Stamina → Power → Guts → Wit
- Each tab contains: facility icon, facility name, current level indicator (Lv1–Lv5), participant avatar row, predicted gain preview

**States:**

| State                | Visual                               | Trigger                                                             |
| -------------------- | ------------------------------------ | ------------------------------------------------------------------- |
| `default`            | Neutral border, level badge grey     | Not selected                                                        |
| `available`          | Colored border matching stat type    | At least one support card present                                   |
| `friendship-ready`   | Rainbow/orange glow border           | A card with bond ≥ 80 is on its matching facility                   |
| `summer-camp`        | Gold border + "Lv5" forced badge     | During summer camp window (first half July to second half August)   |
| `selected`           | Filled background, elevated shadow   | User taps                                                           |
| `disabled`           | Greyed out, no interaction           | Failure risk at 100% or scenario lock                               |
| `wit-special`        | Green tint + "+Energy" icon          | Wit tab always shows energy recovery instead of cost                |

**Data Displayed:**

- Facility level: integer 1–5 (levels up every 4 uses in URA/Trackblazer; tied to team rank in Unity Cup)
- Predicted stat gains: main stat +X, secondary stat +Y, SP +Z
- Energy cost: −17 to −28 (Wit shows "+5" recovery)
- Participant count: number of support card avatars on tile
- Failure risk percentage (if Energy < 50)

**Interactions:**

- Tap → expands into training detail panel (UM-TRN-002)
- Long-press → tooltip showing exact gain formula breakdown
- During summer camp: all five tabs display "Lv5" simultaneously regardless of individual usage count

**Edge Cases:**

- Unity Cup scenario: level is not usage-based but team-rank-based; tab shows team stat rank (F/G = Lv1, D/E = Lv2, B/C = Lv3, A = Lv4, S = Lv5)
- Trackblazer scenario: shop items can temporarily raise a facility level by +1; show a "+1 item" badge
- Grand Masters: Darley Arabian's one-turn buff can push all facilities past Lv5 (red border); show "Lv6" with red glow

**Accessibility:**

- Each tab has `aria-label` including facility name, level, energy cost, and participant count
- Color is never the sole indicator of friendship-ready state; add a sparkle icon overlay
- Minimum tap target: 48×48px

---

#### UM-TRN-002: Training Detail Panel

**Purpose:** Expanded view shown when a facility tab is selected, displaying all participants and predicted outcomes before commit.

**Layout:**

- Top row: predicted stat gains (main, secondary, SP) with mood multiplier applied
- Middle row: support card avatars currently on this tile, each showing bond gauge segment and friendship-ready indicator
- Bottom row: two action buttons — "Train" (primary) and "Cancel" (secondary)

**States:**

| State                  | Condition                               | Display                                                                  |
| ---------------------- | --------------------------------------- | ------------------------------------------------------------------------ |
| `normal`               | Energy > 50                             | Standard layout                                                          |
| `low-energy-warning`   | Energy ≤ 50                             | Yellow warning banner: "Failure risk elevated"                           |
| `critical-energy`      | Energy < 17                             | Red warning banner: "High failure risk"                                  |
| `friendship-active`    | ≥1 card at bond ≥ 80 on matching tile   | Rainbow highlight on qualifying cards; multiplier shown: e.g., "×1.25"   |
| `multi-friendship`     | ≥2 cards qualifying                     | Show multiplicative formula: "×1.25 × ×1.30 = ×1.625"                    |
| `participant-bonus`    | Multiple cards on tile                  | Show "+5% per additional participant" line                               |

**Data Displayed:**

- Exact predicted gains: `(base + bonus) × growth rate × mood term × friendship bonus × participant bonus`
- Mood term displayed as current state name + percentage (e.g., "Good: +10%")
- Energy cost or recovery
- Failure probability percentage (if applicable)
- For Wit: show "Recovers Energy" instead of cost

**Interactions:**

- "Train" button → triggers training animation → resolves to UM-TRN-003 (success) or UM-TRN-004 (failure)
- "Cancel" → returns to UM-TRN-001 tab bar
- Tapping a support card avatar → shows that card's bond gauge and hint pool

---

#### UM-TRN-003: Training Success Result Modal

**Purpose:** Post-training feedback showing stat gains, SP earned, and any triggered events.

**Layout:**

- Animated stat count-up numbers (main stat, secondary stat, SP)
- Mood indicator (unchanged or shifted)
- Event trigger notification (if a support card event fired)
- "Continue" button

**States:**

- `standard`: normal gains
- `friendship-bonus`: rainbow border, "Friendship Training!" banner, amplified numbers
- `event-triggered`: additional panel showing event dialogue and choice buttons
- `summer-camp`: "Summer Camp Bonus" badge if during the Lv5 window

**Data Displayed:**

- Each stat gained with + prefix and animated count
- SP gained
- Bond gauge progress for each participating card (shown as +5 segments filling)
- If a skill hint was granted: hint name, skill name, level, and discounted cost

---

#### UM-TRN-004: Training Failure Result Modal

**Purpose:** Negative feedback when training fails due to low Energy.

**Layout:**

- Red/dark overlay
- Failure type indicator (one of three penalties)
- Penalty description
- "Continue" button

**Failure Types (mutually exclusive per failure):**

| Type          | Icon   | Description              | Stat Impact                                                           |
| ------------- | ------ | ------------------------ | --------------------------------------------------------------------- |
| Energy Loss   | ⚡↓    | "Lost Energy"            | Additional Energy drain beyond the training cost                      |
| Mood Drop     | 😞     | "Motivation decreased"   | Mood drops one state (e.g., Good → Normal)                            |
| Injury        | 🩹     | "Sustained an injury"    | One random stat reduced by −5 to −10; negative status label applied   |

**Edge Cases:**

- Whether Wit training can fail is unresolved across sources (Conflict Log row 2). The component must handle both possibilities: show a failure modal if triggered, but design copy that acknowledges ambiguity ("Unexpected setback during Wit training")
- Pal-type support cards with Failure Protection (effect id 27) reduce failure probability; if protection activated, show a "Protected by [Card Name]" line and suppress the penalty

**Accessibility:**

- Failure type is communicated via icon + text + color (never color alone)
- Haptic feedback pattern differs per failure type

---

#### UM-TRN-005: Energy Meter

**Purpose:** Persistent resource bar showing remaining training Energy.

**Layout:**

- Horizontal bar, fixed position (bottom-left of training screen)
- Numeric overlay: "Energy: 72/100"
- Threshold markers at 50 (yellow) and 25 (red)
- Maximum Energy indicator (base 100, can be raised to 112 via event chains)

**States:**

| State        | Condition              | Visual                                |
| ------------ | ---------------------- | ------------------------------------- |
| `healthy`    | > 50                   | Green fill                            |
| `caution`    | 26–50                  | Yellow fill, subtle pulse animation   |
| `critical`   | ≤ 25                   | Red fill, rapid pulse, warning icon   |
| `full`       | = max                  | Solid green, "MAX" label              |
| `boosted`    | Max raised above 100   | Extended bar segment in blue          |

**Interactions:**

- Tap → expands to show: current value, max value, "Rest" action preview (+30), Wit training recovery preview (+5)
- When Energy < 50, the meter itself shows a tooltip: "Failure risk increases significantly below 50 Energy"

**Data Sources:**

- Rest action: +30 Energy per standard rest
- Wit training: costs 0, recovers small amount
- Event chains: can raise maximum by +12
- Support card effects: Energy Cost Reduction (effect id 28) lowers per-session spend
- Pal card events: can restore Energy directly

---

#### UM-TRN-006: Mood (Motivation) Indicator

**Purpose:** Persistent badge showing current mood state, which modifies training gains and pre-race stats.

**Layout:**

- Circular icon with facial expression, positioned top-right of training screen
- Text label below: state name
- Multiplier badge: "+20%" or "−10%" etc.

**Five States:**

| State (JP)   | State (EN)       | Training Effect   | Pre-Race Effect   | Icon                      |
| ------------ | ---------------- | ----------------- | ----------------- | ------------------------- |
| 絶好調       | Peak Condition   | +20%              | +10%              | Radiant smile, sparkle    |
| 好調         | Good             | +10%              | +5%               | Smile                     |
| 普通         | Normal           | 0%                | 0%                | Neutral                   |
| 不調         | Poor             | −10%              | −2%               | Frown                     |
| 絶不調       | Worst            | −20%              | −5%               | Dark cloud, heavy frown   |

**Interactions:**

- Tap → shows tooltip: "Mood affects training gains and race-day stats. Raise it via Rest or Outings."
- Mood change animation: icon transitions with a brief glow (positive) or dim (negative)

**Edge Cases:**

- `[Global]` client strings for the five states are ❌ UNVERIFIED; use "Mood" as the category label
- Mood does not affect Wit training's Energy recovery, only its stat yield

---

#### UM-TRN-007: Summer Camp Banner

**Purpose:** Temporary overlay indicating the summer camp window is active.

**Layout:**

- Full-width banner at top of training screen
- Sun/beach icon + "Summer Camp Active" text
- Subtext: "All training facilities set to Lv5 for 4 turns"
- Turn counter: "Turns remaining: 3/4"

**Timing:**

- Activates: first half of July (Classic and Senior years)
- Duration: exactly 4 turns
- Deactivates: second half of August

**Behavior:**

- All five facility tabs (UM-TRN-001) display forced Lv5 regardless of individual usage
- Energy costs increase due to higher level
- Strategic planning incentive: users should hoard Energy, items, and buffs before this window

---

#### UM-TRN-008: Bond Gauge (per Support Card)

**Purpose:** Progress bar showing relationship level between trainee and a support card, gating Friendship Training.

**Layout:**

- Horizontal segmented bar (4 visible segments), attached to each support card avatar
- Numeric overlay: "Bond: 65/100"
- Color transitions: yellow (filling) → orange (≥ 80, friendship-ready)

**Thresholds:**

| Value    | State                  | Visual                                |
| -------- | ---------------------- | ------------------------------------- |
| 0–19     | Empty                  | Grey segments                         |
| 20–39    | Filling                | Yellow, 1 segment                     |
| 40–59    | Filling                | Yellow, 2 segments                    |
| 60–79    | Filling                | Yellow, 3 segments                    |
| 80–100   | **Friendship Ready**   | **Orange, 4 segments, glow effect**   |

**Mechanics:**

- Increases by ~+5 per joint training session
- "Starting Bond Up" (effect id 14) raises initial value; present on 289 of 296 JP SSR stat cards
- At 80+, the card can trigger Friendship Training when on its matching facility type
- Pal cards do not have Friendship Bonus (effect id 1) but do have Event Effectiveness (id 26)

**Interactions:**

- Tap → shows card detail (UM-SC-001)
- When threshold crossed: brief animation + toast "Bond reached 80! Friendship Training available"

---

### 1.2 Race System Components

#### UM-RAC-001: Race Viewer / Progress Track

**Purpose:** Horizontal timeline showing race progression across four phases.

**Layout:**

- Horizontal track with 24 sections grouped into 4 phases
- Runner icon moving left-to-right
- Phase boundaries marked at 16.67%, 66.67%, 83.33%
- Skill activation cut-ins appear as overlay animations

**Phase Breakdown:**

| Phase         | ID    | Sections   | Share of Race   | What Is Decided                          |
| ------------- | ----- | ---------- | --------------- | ---------------------------------------- |
| Opening Leg   | 0     | 1–4        | First 1/6       | Gate break, early lead contest           |
| Middle Leg    | 1     | 5–16       | 1/6 to 2/3      | Position holding, then position battle   |
| Final Leg     | 2     | 17–20      | 2/3 to 5/6      | The "move" (仕掛け) begins               |
| Last Spurt    | 3     | 21–24      | Last 1/6        | Who holds on                             |

**Data Displayed:**

- Current phase name (highlighted)
- Runner position (1st–18th depending on field size)
- Endurance bar (Stamina converted to HP at race start)
- Active skill indicators
- "Running Hot" (掛かり) warning if triggered

**Interactions:**

- During playback: tap runner icon → shows current stats, active skills, position
- Speed controls: 1×, 2×, skip
- After race: tap any phase segment → replays that segment

**Edge Cases:**

- Field size varies: 18 runners (152 race rows), 16 (134), 14 (23), and splits across 15, 20, 9, 17, 12, 5
- Champions Meeting: always 9 runners per race
- `positionKeepEnd` sits between 41.64% and 41.70% of race length in all 138 courses (section-10 boundary)

---

#### UM-RAC-002: Phase Indicator

**Purpose:** Segmented progress bar showing which race phase is active.

**Layout:**

- 4-segment horizontal bar above the race viewer
- Active segment highlighted with phase color
- Phase name label below active segment

**Phase Colors:**

- Opening Leg: blue
- Middle Leg: green
- Final Leg: orange
- Last Spurt: red

**Data Displayed:**

- Section counter: "Section 12/24"
- Percentage through race: "52%"
- Phase-specific mechanics active:
  - Opening: gate break status, slow start indicator
  - Middle: position battles (compete_fight_count), lane changes
  - Final: spurt trigger geometry (corner/straight/uphill/downhill)
  - Last Spurt: endurance check, HP remaining percentage

---

#### UM-RAC-003: Skill Activation Cut-In

**Purpose:** Dramatic overlay animation when a skill triggers during a race.

**Layout:**

- Full-screen or half-screen character portrait flash
- Skill name in stylized text
- Brief effect description
- Duration: 1–2 seconds (skippable)

**Variants:**

- `standard`: white/blue border, normal skill
- `rare`: gold border, rare skill
- `unique`: character-specific animation, unique skill
- `evolved`: special evolution animation, evolved skill (new name, new icon)
- `inherited`: shows ancestor's portrait briefly, inherited unique skill

**Interactions:**

- Tap → skip cut-in
- Settings toggle: "Show all cut-ins" / "Show rare+ only" / "Skip all"
- In Champions Meeting: cut-ins are compressed to reduce match duration

**Edge Cases:**

- Multiple skills triggering simultaneously: queue cut-ins sequentially
- Evolved skills show a transformation animation from base to evolved form
- Unique skills have character-specific voice lines

---

#### UM-RAC-004: Race Result Screen

**Purpose:** Post-race summary showing placement, stats earned, and progression.

**Layout:**

- Placement banner: "1st Place!" with rank number
- Stat gains from race (affected by Race Bonus effect id 15)
- SP earned (scales with race difficulty)
- Fan count gained
- Objective completion check (if applicable)
- Skill hint notifications
- Buttons: "Continue" / "View Replay"

**Data Displayed:**

- Placement: 1st through field size
- Fans gained: varies by race grade (G1: 20–54, G2: 13–56, G3: 9–53, OP: 5–50)
- SP gained: scales with difficulty
- Stat gains: modified by Race Bonus support effect
- If a scenario objective was met: green checkmark + objective description

**States:**

- `victory`: gold theme, confetti animation
- `placement`: silver/bronze theme based on rank
- `loss`: neutral theme, encouraging copy
- `objective-met`: additional green banner "Objective Complete!"
- `objective-failed`: red banner with retry option (Alarm Clock)

---

#### UM-RAC-005: Course Condition Display

**Purpose:** Pre-race information panel showing all environmental factors.

**Layout:**

- Grid of condition tags: Surface, Distance, Turn Direction, Season, Weather, Ground, Time of Day
- Each tag has an icon and text label

**Data Fields:**

| Field      | Example Values                          | Source                          |
| ---------- | --------------------------------------- | ------------------------------- |
| Surface    | Turf / Dirt                             | `ground_type` 1 or 2            |
| Distance   | 2,200m (Medium)                         | Course meters + distance_type   |
| Turn       | Right-Handed / Left-Handed / Straight   | Track config                    |
| Season     | Spring / Summer / Autumn / Winter       | `season` 1–4                    |
| Weather    | Sunny / Cloudy / Rain / Snow            | `weather` 1–4                   |
| Ground     | Firm / Good / Soft / Heavy              | `ground_condition` 1–4          |
| Time       | Day / Night                             | `time` value                    |

**Mechanical Notes:**

- Rain (`weather==3`) guarantees ground becomes Soft or Heavy (`condition` 3 or 4), never Firm or Good
- Heavy ground (`condition==4`) applies −50 Speed modifier to both Turf and Dirt
- Snow (`weather==4`) occurs only in Winter (`season==4`)
- 13 of 322 race rows are night meetings (`time==4`)

**Interactions:**

- Tap any tag → tooltip explaining mechanical impact
- Weather/ground tags show skill compatibility: "Night Races ◎ active" or "disabled in this edition"

---

#### UM-RAC-006: Running Strategy Selector

**Purpose:** Pre-race or pre-build selection of one of four running strategies.

**Layout:**

- Four selectable cards in a 2×2 grid
- Each card: strategy icon, name, brief description, skill pool count

**Four Strategies:**

| ID    | JP    | Global Label   | Description                           | Skill Pool   |
| ----- | ----- | -------------- | ------------------------------------- | ------------ |
| 1     | 逃げ  | Front Runner   | Leads from start; phase 0–1 toolkit   | 107 skills   |
| 2     | 先行  | Pace Chaser    | Mid-race positioning; largest pool    | 220 skills   |
| 3     | 差し  | Late Surger    | Late-race move from final corner      | 166 skills   |
| 4     | 追込  | End Closer     | Last spurt closer                     | 115 skills   |

**Mechanical Notes:**

- A skill gated to `running_style==N` cannot fire for any other style
- "Runaway" (大逃げ) is NOT a fifth strategy; it is gated behind `running_style==1`
- Uphill skills are Front Runner flavored; downhill skills are End Closer flavored
- Strategy label rewrites the usable skill list, not a hidden speed curve

**Interactions:**

- Select → filters skill shop to show compatible skills
- Shows aptitude grade for selected strategy (S–G)
- Warning if aptitude is below C: "Low aptitude will significantly reduce effectiveness"

---

### 1.3 Support Card Components

#### UM-SC-001: Support Card Detail View

**Purpose:** Full information panel for a single support card.

**Layout:**

- Card artwork (top half)
- Card name, rarity, type, level
- Support effects list with values
- Event chain preview
- Hint skill pool
- Bond gauge
- Limit break / release stage indicator

**Data Displayed:**

| Field             | Source                                                         |
| ----------------- | -------------------------------------------------------------- |
| Card name         | `support-cards.json`                                           |
| Rarity            | R / SR / SSR (export `rarity` 1/2/3)                           |
| Type              | Speed / Stamina / Power / Guts / Wit / Pal / Group             |
| Level             | Current level (JP: removed 2025-10-07; Global: still active)   |
| Limit Break       | 0–4 breaks (5 copies for full)                                 |
| Support effects   | List of effect IDs with values                                 |
| Hint skills       | Pool of skills this card can hint                              |
| Event chain       | Number of events in chain                                      |

**Server Differences:**

- `[JP]`: Card levels and Support Pt removed at 2025-10-07 maintenance; 上限解放 renamed 性能解放; duplicate applies release stage automatically
- `[Global]`: Original model retained; four breaks via consuming copies; "Limit Break" terminology; Uncap Crystals as items

**Interactions:**

- Tap effect → tooltip explaining mechanical impact
- Tap hint skill → shows skill detail and current hint level
- "Add to Deck" button (if in deck-building mode)

---

#### UM-SC-002: Support Card Type Badge

**Purpose:** Visual indicator of card type, used across all card displays.

**Seven Types:**

| Type Key       | JP Label   | Global Label   | Color    | Count (JP SSR)   | Count (Global SSR)   |
| -------------- | ---------- | -------------- | -------- | ---------------- | -------------------- |
| speed          | スピード   | Speed          | Red      | 72               | 28                   |
| stamina        | スタミナ   | Stamina        | Blue     | 55               | 22                   |
| power          | パワー     | Power          | Orange   | 57               | 21                   |
| guts           | 根性       | Guts           | Yellow   | 59               | 20                   |
| intelligence   | 賢さ       | Wit            | Green    | 53               | 18                   |
| friend         | 友人       | Pal            | Pink     | 11               | 4                    |
| group          | グループ   | Group          | Purple   | 5                | 2                    |

**Mechanical Notes:**

- Pal cards: none carry Friendship Bonus (id 1); all carry Event Effectiveness (id 26); most carry Failure Protection (id 27) and Event Recovery (id 25)
- Group cards: SSR-only on both servers; carry full friendship kit plus Wit Friendship Recovery (id 31)
- Group card events can involve multiple Umamusume acting as a single support card (e.g., entry 30081 records six character IDs)

---

#### UM-SC-003: Card Event Chain Modal

**Purpose:** Narrative dialogue triggered during a run, presenting choices that affect stats, mood, and skill hints.

**Layout:**

- Character portrait(s)
- Dialogue text
- Choice buttons (typically 2–3 options)
- Reward preview for each choice

**Event Types:**

- Per-character chains: 135 in export
- Pal chains: 11 in export
- Group chains: 5 in export

**Choice Outcomes (typical):**

- Stat gains (specific stats)
- Energy recovery
- Mood increase/decrease
- Bond gauge increase
- Skill hint (with level)
- Status condition heal

**Interactions:**

- Select choice → applies reward → closes modal → returns to training screen
- Some events have conditional branches based on prior choices or stat thresholds
- Hint level from card affects skill hint payout: gain is `1 + hint level`

**Edge Cases:**

- Group card events may show multiple character portraits
- Scenario-linked cards have upgraded event rewards when scenario link is active
- In idle training mode (`[JP]` 自主トレ育成), certain events had reward bugs (fixed 2026-09-25, compensation: Toughness 30 ×4)

---

### 1.4 Skill System Components

#### UM-SKL-001: Skill Shop / Acquisition Menu

**Purpose:** Interface for spending Skill Points (SP) to learn skills.

**Layout:**

- SP balance display (top-right)
- Category filter tabs (horizontal scroll)
- Skill grid (2-column list)
- Each skill entry: icon, name, rarity, cost, hint level, activation condition

**Category Filters:**

- All
- Speed / Recovery / Acceleration / Positioning / Debuff / Unique / Evolved
- Distance-specific: Sprint / Mile / Medium / Long
- Strategy-specific: Front Runner / Pace Chaser / Late Surger / End Closer
- Course-specific: Corner / Straight / Uphill / Downhill
- Condition-specific: Weather / Ground / Time / Popularity

**Data Displayed per Skill:**

- Skill name (JP and Global if different)
- Rarity: Normal (white) / Rare (gold) / Unique / Evolved
- SP cost (base cost minus hint discount)
- Hint level: 0–5 (each level reduces cost by ~10%, max 30% at Lv3 via items)
- Activation condition text (e.g., "Recover endurance on a corner with efficient turning")
- Running style gate (if applicable)
- Phase gate (e.g., `phase>=2`, `is_lastspurt==1`)

**Interactions:**

- Tap skill → expands to UM-SKL-002 (detail view)
- "Learn" button → deducts SP → skill added to loadout
- Filter/sort by cost, rarity, hint level
- `[JP]` bulk tool: "スキルセット" feature (added 2026-09-11) sorts skills into three priority groups (超優先 / 優先 / 通常), shares by ID, allocates SP to whole set in one action

**SP Sources:**

- Training sessions: Wit pays +4 to +5 per session; other four pay +2
- Races: payout scales with difficulty
- Training events
- Support card effects (Race Bonus id 15 raises post-race package)

---

#### UM-SKL-002: Skill Detail Card

**Purpose:** Expanded view of a single skill with full mechanical description.

**Layout:**

- Skill icon (color-coded by rarity)
- Skill name (large)
- Rarity badge
- Full activation condition text
- Effect description
- Cost display: base cost → hint discount → final cost
- "Learn" or "Learned" button
- If evolved: shows base → evolved transformation

**Data Displayed:**

- Base SP cost (e.g., 170 for Corner Recovery ○, 340 for Arc Line Maestro)
- Hint level discount: each level = −10% cost (Lv3 = −30%)
- Activation conditions parsed into readable tags:
  - Phase: "Activates in Final Leg"
  - Position: "When positioned toward the front (order_rate ≤ 50)"
  - Corner: "On corner 2"
  - Running style: "Front Runner only"
  - Weather: "On rainy days"
  - Ground: "On soft or heavy ground"

**Evolved Skill Display:**

- Shows base skill name → evolved skill name
- Evolution conditions listed (e.g., "Win a specific G1 race", "Reach 1200 Power")
- Stat comparison: base effect vs. evolved effect
- Unique to each character: every character gets exactly 2 potential evolved skills with unique names

**Interactions:**

- "Learn" → deducts SP, adds to active skill list
- If already learned and evolution conditions met: "Evolve" button appears
- If hint level > 0: shows "Hint Lv.X: −Y% cost"

---

#### UM-SKL-003: Hint Level Indicator

**Purpose:** Visual badge showing accumulated hint level for a specific skill.

**Layout:**

- Small badge attached to skill icon
- Level number: "Hint Lv.3"
- Discount percentage: "−30%"

**Mechanics:**

- Hints come from: support card events, training events, inheritance (white factors give +1 to +5)
- Each hint level reduces SP cost by ~10%
- Item route caps at Lv3 (30% reduction): requires ヒント本 ×12, ヒント専門書 ×6, 夢の煌めき ×30
- A card's own hint level multiplies event payout: gain = `1 + hint level`
- `[Global]` tutorial names these "Books of Hints"

**States:**

- `none`: no badge
- `lv1` through `lv5`: increasingly prominent badge, cost reduction shown
- `max-item`: "Lv3 (item max)" if reached via books
- `inherited`: special border indicating hint came from inheritance

---

### 1.5 Inheritance Components

#### UM-INH-001: Ancestor Circle Diagram

**Purpose:** Visual representation of the six-member inheritance lineage.

**Layout:**

- Central node: the trainee being trained
- Two parent nodes (left and right)
- Four grandparent nodes (two behind each parent)
- Connecting lines with compatibility grades (△, ○, ◎)

**Data Displayed per Node:**

- Character portrait
- Character name
- Factor tags (color-coded sparks)
- Star ratings per factor
- Compatibility grade to trainee

**Constraints:**

- Two parents must be different characters
- Neither parent may be the trainee herself
- A character may appear anywhere in the lineage as long as she is not her own direct ancestor
- Duplicates deeper in the diagram are allowed but cost compatibility (repeated ancestor contributes compatibility value of 0)
- One parent may be a friend's finished Umamusume, rented from the friend list

**Interactions:**

- Tap parent node → shows that parent's factors and star ratings
- Tap compatibility line → tooltip explaining grade and shared relationships
- "Change" button per slot → opens ancestor picker
- "Rent from Friend" button for one parent slot

---

#### UM-INH-002: Factor Tag Display

**Purpose:** Color-coded tag showing a single inheritable factor.

**Factor Categories:**

| Category       | JP Name              | Global Name                 | Color   | Effect                                                       |
| -------------- | -------------------- | --------------------------- | ------- | ------------------------------------------------------------ |
| Stat           | 青因子               | Blue Spark                  | Blue    | One stat +5/+12/+21 at career start (1/2/3 stars)            |
| Aptitude       | 赤因子               | Pink Spark                  | Pink    | Track/distance/strategy: +1 to +4 grades                     |
| Unique Skill   | 緑因子 / 固有因子    | Green Spark                 | Green   | Carries character's unique skill as 1–3 hint levels          |
| Skill          | 白因子（スキル因子） | White Spark (skill)         | White   | Mid-run hint +1 to +5, or small stat top-up                  |
| Competition    | 白因子（レース因子） | White Spark (competition)   | White   | From G1 wins; 3/6/9 stat per event                           |
| Scenario       | シナリオ因子         | Scenario Spark              | Gold    | From clearing scenario's final conditions; ~10–30 per stat   |

**Star Rating Display:**

- 1–3 stars per factor
- More stars = larger payout + better trigger odds
- Stat factor star odds based on final stat value:
  - Below 600: ~90% 1★, ~10% 2★, 0% 3★
  - 600–1100: ~50% 1★, ~45% 2★, ~6% 3★
  - Above 1100: ~20% 1★, ~70% 2★, ~10% 3★

**Interactions:**

- Tap factor → tooltip explaining effect and payout
- Star rating shown as filled/empty star icons
- Color is the primary category identifier; shape varies for accessibility

---

#### UM-INH-003: Inheritance Event Modal

**Purpose:** Triggered at three fixed moments during a run, showing factor inheritance results.

**Trigger Timings:**

1. Career start (deterministic for stat and aptitude factors)
2. Early April of Classic Year (probabilistic)
3. Early April of Senior Year (probabilistic)

**Layout:**

- Ancestor portrait(s) whose factor triggered
- Factor type and star rating
- Effect description (e.g., "Speed +12", "Mile aptitude raised by 1 grade")
- "Golden inheritance" variant: rare event with enhanced payout

**States:**

- `standard`: normal inheritance event
- `golden`: gold border, enhanced rewards, celebratory animation
- `white-factor`: only fires mid-run, never at career start
- `no-trigger`: if no factor fires, show "No inspiration this time"

**Edge Cases:**

- White factors never fire at career start, only mid-run
- A rare golden inheritance event hands over more than a normal one
- Aptitude inheritance cannot lift above A before run starts; cannot lift more than 4 grades; cannot take F or below all the way to A
- Mid-run events can push A to S; a mid-run jump is worth one grade regardless of star count

---

### 1.6 Scenario Components

#### UM-SCN-001: Scenario Selector

**Purpose:** Pre-run screen for choosing which training scenario to play.

**Layout:**

- Carousel or grid of scenario cards
- Each card: scenario name, icon, brief description, stat caps, unique mechanic summary

**Current Scenarios (from `scenarios.json`, 14 rows):**

| Order   | JP Title                    | Global Title                          | JP Since     | Global Since   |
| ------- | --------------------------- | ------------------------------------- | ------------ | -------------- |
| 1       | 新設！URAファイナルズ       | URA Finale                            | 2021-02-24   | 2025-06-26     |
| 2       | アオハル杯                  | Unity Cup                             | 2021-08-30   | 2025-11-06     |
| 3       | Make a new track!!          | Trackblazer / Twinkle Star Climax     | 2022-02-24   | 2026-03-12     |
| 4       | つなげ、照らせ、ひかれ。    | Brighter Together Our Grand Concert   | 2022-08-24   | 2026-07-22     |
| 5–14    | Various JP-only scenarios   | No Global release                     | 2023–2026    | N/A            |

**Data Displayed per Scenario:**

- Stat caps (Speed/Stamina/Power/Guts/Wit)
- Unique mechanic summary
- Current scenario status (active, selectable)

**Stat Caps by Scenario:**

| Scenario               | SPD    | STA    | PWR    | GUT    | WIT    |
| ---------------------- | ------ | ------ | ------ | ------ | ------ |
| URA Finale             | 1400   | 1400   | 1400   | 1400   | 1400   |
| Unity Cup              | 1300   | 1300   | 1300   | 1300   | 1800   |
| Trackblazer/Climax     | 1200   | 1900   | 1200   | 1500   | 1200   |
| Grand Concert          | 1600   | 1300   | 1300   | 1500   | 1300   |
| Grand Masters (ext.)   | 1500   | 1400   | 1500   | 1300   | 1300   |
| L'Arc (ext.)           | 1600   | 1600   | 1500   | 1500   | 1300   |

Hard ceiling across all scenarios: 2000 per stat (from `scenarios.json`).

---

#### UM-SCN-002: Objective Tracker

**Purpose:** Persistent panel showing current scenario objectives and progress.

**Layout:**

- Collapsible panel (top-right of main screen)
- Objective description text
- Progress indicator (race placement, fan count, or scenario-specific metric)
- Deadline: in-game date
- Status: Active / Complete / Failed

**Objective Types:**

- Race placement: "Finish 3rd or better in [Race Name]"
- Fan acquisition: "Reach [X] fans by [Date]"
- Scenario-specific: varies (Grade Points in Trackblazer, team rank in Unity Cup)

**Interactions:**

- Tap → expands to show full objective chain
- Completed objectives: green checkmark
- Failed mandatory objectives: red X + "Retry with Alarm Clock" option
- Alarm Clock: consumable item that allows retrying a missed mandatory placement

---

### 1.7 Live Operations Components

#### UM-LIV-001: Event Banner Carousel

**Purpose:** Home-screen carousel displaying active and upcoming events.

**Layout:**

- Horizontal scrollable carousel
- Each banner: event art, event name, time remaining, primary reward preview
- Priority ordering: active events first, upcoming second

**Event Types to Display:**

- Champions Meeting (race event)
- Story events
- Legend Race
- Masters Challenge
- Training Pass
- Campaign events (Autumn G1, Anniversary)
- Transfer Requests (`[Global]`)

**Data Displayed:**

- Event name
- Start/end dates with countdown timer
- Primary reward icons
- Server tag: `[JP]` or `[Global]`

**Interactions:**

- Tap banner → navigates to event detail screen
- Swipe → next/previous banner
- "i" icon → tooltip with event rules summary

---

#### UM-LIV-002: Champions Meeting Bracket

**Purpose:** Tournament progression display for Champions Meeting PvP mode.

**Layout:**

- Three-round bracket: Round 1 → Round 2 → Final
- Group A / Group B split shown after each round
- League indicator: Graded League / Open League

**Progression Rules:**

- Each entry: 5 races, 3 trainers matched per race, 9 runners per race
- Up to 4 entries per day
- 3+ wins → Group A of next round
- <3 wins → Group B
- 0 wins in Round 2 → eliminated
- Final round: one race between three trainers

**League Eligibility:**

- `[Global]` Open League: Career Rank A+ or below; S+ blocked
- `[JP]` Open League: 育成ランク[UC] or below; [UC1]+ blocked
- Graded League: no rank limit on either server

**Data Displayed:**

- Current round and group
- Win/loss record
- Remaining entries today
- Skill disable list for this edition
- Special rules (e.g., "デバフなし" / no debuff)

**Interactions:**

- "Enter" button → consumes Entry Ticket or 30 Carats
- "View Rules" → shows full edition rules and disabled skills
- "Register Team" → selects 3 Veteran Umamusume

---

#### UM-LIV-003: Team Trials Score Display

**Purpose:** Weekly team competition dashboard.

**Layout:**

- Team composition: 5 categories (Sprint, Mile, Medium, Long, Dirt)
- Score breakdown per category
- Class placement: Class 6 through Class 1
- Weekly reset timer

**Data Displayed:**

- Team members per category (up to 3 per race)
- Score from finishes, gate position, opponent strength
- Class endpoint rewards: e.g., holding Class 6 pays 250 Carats + 5000 Friend Points
- Weekly reset: Monday 15:00 UTC
- Entry cost: 1 RP per entry

**Interactions:**

- "Set Team" → team composition screen
- "Race" → consumes RP, runs 5 races
- "View Rewards" → class endpoint reward table
- Weather/gate/mood consumables can be used here (Pleasing Parfait, Sunshine Doll, etc.)

---

### 1.8 Gacha Components

#### UM-GAC-001: Scout Banner

**Purpose:** Gacha/Scout interface for acquiring new characters and support cards.

**Layout:**

- Banner art (featured character/card)
- Banner name and type
- Featured unit display with rarity
- Rate summary
- Exchange Point progress bar
- Pull buttons: Single / 10-pull / Paid 10-pull (if applicable)
- Countdown timer to banner close

**Banner Types:**

- `[JP]`: ピックアッププリティーダービーガチャ, セレクトピックアップ, トゥインクルコレクション, SSR確定, ★3確定
- `[Global]`: Spotlight Pretty Derby Scout, Spotlight Support Card Scout

**Rate Display (standard structure):**

| Rarity     | Rate                              |
| ---------- | --------------------------------- |
| ★3 / SSR   | 3.00% total (featured at 0.75%)   |
| ★2 / SR    | 18.00%                            |
| ★1 / R     | 79.00%                            |

**Exchange / Spark System:**

| Item                | JP                                      | Global                                                   |
| ------------------- | --------------------------------------- | -------------------------------------------------------- |
| Currency name       | 育成ウマ娘交換Pt / サポートカード交換Pt | Trainee Exchange Points / Support Card Exchange Points   |
| Cost for featured   | 200 Pt                                  | 200 Points                                               |
| Earn rate           | 1 Pt per pull                           | 1 Point per roll                                         |
| Carryover           | Never                                   | Never                                                    |
| Expired balance     | Auto-converted to クローバー (Clovers)  | Auto-converted to Clovers                                |

**Interactions:**

- "Pull" → triggers UM-GAC-002 (pull animation)
- "Rates" → expands to full rate table (in-app only, not on web)
- "Exchange" → opens exchange shop at 200 Pt
- "Featured" → shows featured unit details

**Edge Cases:**

- Paid 10-pull (有償ジュエル 1500) may carry a guaranteed ★3/SSR on slot 10; normal paid 10-pull does not
- One-per-account limits on certain guaranteed banners
- Daily limited single pull at 50 有償ジュエル (`[JP]`)
- Rates are ❌ UNVERIFIED on web; only available in-app [ガチャ詳細] / Scout Rates tab

---

#### UM-GAC-002: Pull Animation

**Purpose:** Dramatic reveal sequence for gacha results.

**Layout:**

- Full-screen animation sequence
- Rarity indicated by visual effects before reveal
- Character/card portrait reveal
- New unit: "NEW!" badge
- Duplicate: converts to exchange points / Star Pieces

**Rarity Indicators (pre-reveal):**

- `[JP]` character gacha: gate color (rainbow = ★3), chairman appearance = ★3, decorated door = ★3
- `[JP]` support gacha: rainbow sticky note = SSR, rainbow cover = SSR, night background = 2+ SSR
- `[Global]`: similar visual hierarchy

**Interactions:**

- Tap to skip animation
- After reveal: "Pull Again" / "Go to Deck" / "Exchange"
- 10-pull: sequential reveals with skip option, summary at end

---

### 1.9 Terminology & Server Components

#### UM-TRM-001: Server Terminology Map

**Purpose:** Internal reference component mapping JP and Global terminology.

**Critical Mappings:**

| Concept                    | JP                                             | Global                                         |
| -------------------------- | ---------------------------------------------- | ---------------------------------------------- |
| Gacha system               | ガチャ                                         | Scouts                                         |
| Pull currency              | ジュエル                                       | Carats                                         |
| Friend-type card           | 友人                                           | Pal                                            |
| Inheritance factor         | 因子                                           | Spark                                          |
| Finished character         | 殿堂入りウマ娘                                 | Veteran Umamusume                              |
| Character being trained    | 育成ウマ娘                                     | Trainee Umamusume                              |
| Team race mode             | チーム競技場                                   | Team Trials                                    |
| Champions Meeting naming   | Category tag (SPRINT/MILE/CLASSIC/LONG/DIRT)   | Zodiac cup (Scorpio Cup, etc.)                 |
| Idle training              | 自主トレ育成                                   | Independent Training (unconfirmed on Global)   |

**Design Rule:** Never mix server terminology in a single UI. All labels must be resolved to the active server's client strings before rendering.

---

## PART 2: USER FLOW DIAGRAMS

### Flow 1: Pre-Career Setup (New Run)

```text
START: User taps "Career" / "Training" from main menu
│
├─ STEP 1: Scenario Selection (UM-SCN-001)
│   ├─ User browses scenario carousel
│   ├─ Each card shows: name, stat caps, unique mechanic, active status
│   ├─ User selects scenario (e.g., URA Finale, Unity Cup, Trackblazer)
│   └─ CONFIRM: Scenario locked for this run
│
├─ STEP 2: Trainee Selection
│   ├─ User browses trainable Umamusume list (135 units on JP, 68 on Global)
│   ├─ Filters: rarity, aptitude, running style, distance
│   ├─ User selects character + costume variant (268 cards total on JP)
│   ├─ Display: base stats, growth rates, aptitudes, unique skill
│   └─ CONFIRM: Trainee locked
│
├─ STEP 3: Ancestor / Inheritance Setup (UM-INH-001)
│   ├─ User selects 2 ancestors (parents) from finished Umamusume list
│   │   ├─ Constraint: parents must be different characters
│   │   ├─ Constraint: neither can be the trainee
│   │   ├─ Option: rent 1 parent from friend list
│   │   └─ Each parent brings 2 grandparents automatically
│   ├─ System displays 6-member ancestor circle
│   ├─ Compatibility grades calculated: △, ○, ◎
│   ├─ Factor tags displayed per ancestor (Blue/Pink/Green/White)
│   ├─ WARNING if duplicate ancestors detected: "Compatibility reduced"
│   └─ CONFIRM: Ancestor circle locked
│
├─ STEP 4: Support Deck Building (UM-SC-001)
│   ├─ User selects 6 support cards from collection
│   │   ├─ Constraint: no duplicate cards
│   │   ├─ Constraint: 6 slots exactly
│   │   ├─ Types available: Speed, Stamina, Power, Guts, Wit, Pal, Group
│   │   └─ Guidance: 2–3 cards per type recommended; full 6-type deck weak for SP
│   ├─ Scenario-specific requirements shown:
│   │   ├─ Unity Cup: 4+ distinct types unlocks bonus training
│   │   ├─ Trackblazer: specific type floors for shop bonuses
│   │   └─ Grand Masters: "Ancestors & Guides" card is scenario link
│   ├─ WARNING if Pal/Group duplicated: "Not recommended: over-supplies recovery"
│   └─ CONFIRM: Deck locked
│
├─ STEP 5: Trainer Abilities (if applicable)
│   ├─ User equips passive buffs (stat boosts, training effectiveness)
│   └─ CONFIRM
│
├─ STEP 6: Pre-Run Summary
│   ├─ Review screen: scenario, trainee, ancestors, deck, abilities
│   ├─ Projected stat caps displayed
│   ├─ "Begin Training" button
│   └─ CONFIRM: Run begins → Flow 2
│
END: Career run initialized
```text

---

### Flow 2: Core Training Loop (Single Turn)

```text
START: Training turn begins
│
├─ STEP 1: Turn Context Display
│   ├─ Calendar date shown (e.g., "Classic Year, July, 1st Half")
│   ├─ Turn counter: "Turn 24/72"
│   ├─ Current objective shown (UM-SCN-002)
│   ├─ Energy meter displayed (UM-TRN-005)
│   ├─ Mood indicator displayed (UM-TRN-006)
│   └─ If Summer Camp active: UM-TRN-007 banner shown
│
├─ STEP 2: Action Selection
│   ├─ OPTION A: Train (primary path)
│   │   ├─ User views 5 facility tabs (UM-TRN-001)
│   │   ├─ Each tab shows: level, participants, predicted gains, energy cost
│   │   ├─ Friendship-ready tabs glow (bond ≥ 80 + matching facility)
│   │   ├─ User selects a facility tab
│   │   └─ → Training Detail Panel opens (UM-TRN-002)
│   │
│   ├─ OPTION B: Rest
│   │   ├─ Recovers +30 Energy
│   │   ├─ Small chance of mood increase
│   │   └─ → Resolve turn → next turn
│   │
│   ├─ OPTION C: Outing (お出かけ)
│   │   ├─ Raises mood by 1 state
│   │   ├─ May trigger support card event
│   │   ├─ Costs 1 turn (no training)
│   │   └─ → Resolve turn → next turn
│   │
│   ├─ OPTION D: Race (if available)
│   │   ├─ → Flow 4: Race Entry and Resolution
│   │   └─ Returns here after race
│   │
│   ├─ OPTION E: Skill Shop
│   │   ├─ → Flow 5: Skill Acquisition
│   │   └─ Returns here after browsing
│   │
│   └─ OPTION F: Scenario-specific action
│       ├─ Unity Cup: team management
│       ├─ Trackblazer: Special Shop
│       ├─ Grand Masters: Knowledge Table / Goddess activation
│       └─ Returns here after action
│
├─ STEP 3: Training Resolution (if Option A selected)
│   ├─ User confirms training in detail panel
│   ├─ System rolls success/failure based on Energy level
│   │   ├─ Energy > 50: low failure risk
│   │   ├─ Energy ≤ 50: elevated risk (non-linear curve)
│   │   └─ Energy < 17: high risk
│   │
│   ├─ IF SUCCESS → UM-TRN-003: Training Success Result
│   │   ├─ Stats gained: (base + bonus) × growth × mood × friendship × participants
│   │   ├─ SP gained: +2 (or +4 to +5 for Wit)
│   │   ├─ Bond gauge increases for participating cards (~+5)
│   │   ├─ If friendship training: multiplier shown (e.g., ×1.625)
│   │   ├─ If support event triggered: → UM-SC-003 (event modal)
│   │   ├─ If scenario-specific trigger: handle (Spirit Burst, Knowledge Fragment, etc.)
│   │   └─ → Next turn
│   │
│   └─ IF FAILURE → UM-TRN-004: Training Failure Result
│       ├─ Random penalty: Energy loss OR Mood drop OR Injury (−5 to −10 stat)
│       ├─ If Failure Protection active: penalty reduced/suppressed
│       └─ → Next turn
│
END: Turn resolved, calendar advances
```text

---

### Flow 3: Friendship (Rainbow) Training Trigger

```text
START: User selects a training facility
│
├─ CHECK 1: Is any support card on this tile at bond ≥ 80?
│   ├─ NO → Standard training (Flow 2, Step 3)
│   └─ YES → Proceed to CHECK 2
│
├─ CHECK 2: Does the card's type match this facility?
│   ├─ NO → Card present but no friendship trigger; standard training
│   └─ YES → FRIENDSHIP TRAINING ACTIVE
│
├─ CALCULATE MULTIPLIER:
│   ├─ Base gain = facility base + card bonus
│   ├─ × Growth rate (per-stat correction)
│   ├─ × Mood term (1 + mood bonus × Mood Effect / 100)
│   ├─ × Friendship Bonus per qualifying card
│   │   ├─ 1 card: × (1 + bonus/100)
│   │   └─ 2+ cards: MULTIPLICATIVE, not additive
│   │       └─ Example: 1.25 × 1.30 = 1.625
│   ├─ × Training Effectiveness (if active)
│   └─ × Participant bonus (+5% per additional card on tile)
│
├─ DISPLAY:
│   ├─ Rainbow glow on facility tab
│   ├─ "Friendship Training!" banner
│   ├─ Multiplier breakdown shown in detail panel
│   └─ Amplified stat gain preview
│
├─ SPECIAL CASE: Wit friendship training
│   ├─ Returns Energy instead of consuming it
│   ├─ Wit Friendship Recovery (effect id 31) scales the refund
│   └─ Display: "+X Energy recovered" instead of cost
│
└─ RESOLVE: Training executes with amplified gains → UM-TRN-003
```text

---

### Flow 4: Race Entry and Resolution

```text
START: User selects "Race" from action menu
│
├─ STEP 1: Race Selection Screen
│   ├─ List of available races (filtered by scenario, objectives, fan count)
│   ├─ Each race shows:
│   │   ├─ Race name, grade (G1/G2/G3/OP/Pre-OP)
│   │   ├─ Distance band + meters
│   │   ├─ Surface (Turf/Dirt)
│   │   ├─ Weather + Ground condition
│   │   ├─ Fan requirement (fans_needed)
│   │   ├─ Scenario-specific markers (Grade Points in Trackblazer)
│   │   └─ Rival indicator (if applicable)
│   ├─ User selects a race
│   └─ CONFIRM entry
│
├─ STEP 2: Pre-Race Setup
│   ├─ Running strategy confirmed (UM-RAC-006)
│   ├─ Skill loadout reviewed
│   ├─ Course conditions displayed (UM-RAC-005)
│   ├─ Mood applied to base stats (Peak: +10%, Worst: −5%)
│   ├─ If Champions Meeting: skill disable list shown
│   └─ "Start Race" button
│
├─ STEP 3: Race Simulation
│   ├─ Race viewer activates (UM-RAC-001)
│   ├─ Phase progression: Opening → Middle → Final → Last Spurt
│   ├─ Skill activations trigger cut-ins (UM-RAC-003)
│   ├─ "Running Hot" warning if triggered (Wit-gated)
│   ├─ Position battles shown in Middle Leg
│   ├─ Spurt trigger shown in Final Leg (corner/straight/uphill/downhill)
│   └─ Endurance check in Last Spurt (HP vs remaining distance)
│
├─ STEP 4: Race Result (UM-RAC-004)
│   ├─ Placement displayed
│   ├─ IF scenario objective met:
│   │   ├─ Green "Objective Complete!" banner
│   │   └─ Progress tracker updates
│   ├─ IF scenario objective failed:
│   │   ├─ Red banner
│   │   └─ "Retry with Alarm Clock?" option (if mandatory)
│   ├─ Rewards distributed:
│   │   ├─ Fans gained (scales with grade)
│   │   ├─ SP gained (scales with difficulty)
│   │   ├─ Stat gains (modified by Race Bonus)
│   │   └─ Scenario currency (Grade Points, Shop Coins, etc.)
│   └─ "Continue" → returns to training loop
│
END: Race resolved
```text

---

### Flow 5: Skill Acquisition

```text
START: User opens Skill Shop (UM-SKL-001)
│
├─ STEP 1: Browse Skills
│   ├─ SP balance displayed (top-right)
│   ├─ Category filters available
│   ├─ Skills listed with: icon, name, rarity, cost, hint level
│   ├─ Skills gated by running style show lock icon if incompatible
│   └─ Skills gated by phase/condition show requirement text
│
├─ STEP 2: Select Skill
│   ├─ User taps skill → UM-SKL-002 (detail view)
│   ├─ Full activation condition displayed
│   ├─ Cost breakdown: base → hint discount → final
│   ├─ If hint level > 0: "Hint Lv.X: −Y%" shown
│   └─ If evolution conditions met: "Evolve" preview shown
│
├─ STEP 3: Learn Skill
│   ├─ IF SP ≥ final cost:
│   │   ├─ "Learn" button active
│   │   ├─ User taps "Learn"
│   │   ├─ SP deducted
│   │   ├─ Skill added to active loadout
│   │   ├─ Success animation
│   │   └─ Return to skill list
│   │
│   └─ IF SP < final cost:
│       ├─ "Learn" button disabled
│       ├─ "Insufficient SP" message
│       └─ Tooltip: "Earn more SP through training and races"
│
├─ STEP 4 (OPTIONAL): Bulk Allocation [JP only]
│   ├─ User opens "スキルセット" feature
│   ├─ Sorts desired skills into 3 priority groups:
│   │   ├─ 超優先 (Super Priority)
│   │   ├─ 優先 (Priority)
│   │   └─ 通常 (Normal)
│   ├─ Allocates SP to entire set in one action
│   └─ Can share set by ID
│
END: Skill acquired or browsing continues
```text

---

### Flow 6: Inheritance / Factor Farming Loop

```text
START: User completes a career run
│
├─ STEP 1: Factor Generation
│   ├─ System evaluates finished Umamusume's:
│   │   ├─ Final stats → Blue Sparks (stat factors)
│   │   ├─ Aptitudes → Pink Sparks (aptitude factors)
│   │   ├─ Unique skill → Green Spark (guaranteed if character is ★3+)
│   │   ├─ Learned skills → White Sparks (skill factors)
│   │   ├─ G1 wins → White Sparks (competition factors)
│   │   └─ Scenario completion → Scenario Spark
│   ├─ Star ratings rolled based on final values:
│   │   ├─ Stat > 1100: ~10% chance of 3★
│   │   ├─ Stat 600–1100: ~6% chance of 3★
│   │   └─ Stat < 600: 0% chance of 3★
│   └─ Factor set saved to character profile
│
├─ STEP 2: Factor Research (Optional, JP)
│   ├─ Agnes Tachyon factor research event (recurring)
│   ├─ Items can retro-fit generic factors
│   ├─ 究極因子研究: sets aptitude, stat, unique factors to ★3
│   └─ Cannot push past ★4
│
├─ STEP 3: Use as Ancestor
│   ├─ Finished Umamusume can be selected in UM-INH-001
│   ├─ Using as ancestor does NOT consume the character
│   ├─ Factors displayed in ancestor circle
│   └─ Compatibility calculated with new trainee
│
├─ STEP 4: Farming Loop (因子周回)
│   ├─ User runs dedicated careers solely for factor output
│   ├─ Recommended order:
│   │   ├─ Build grandparent tier first (reusable across many parents)
│   │   ├─ Spend research rewards on grandparents, not parents
│   │   ├─ Prioritize: distance aptitude > running style > surface > unique > stat
│   │   └─ Keep white factors in stock (only fire mid-run)
│   ├─ Stack same factor across all 6 diagram members
│   │   └─ Trial data: 11/50 carry with partial setup, 15/50 with full 6-member
│   └─ Repeat until desired factor set achieved
│
END: Factors banked for future runs
```text

---

### Flow 7: Summer Camp Window

```text
START: Calendar reaches July (Classic or Senior year)
│
├─ STEP 1: Summer Camp Activates
│   ├─ UM-TRN-007 banner appears
│   ├─ All 5 facility tabs forced to Lv5
│   ├─ Duration: exactly 4 turns
│   └─ Energy costs increase due to higher level
│
├─ STEP 2: Strategic Window (4 turns)
│   ├─ User should have pre-stocked:
│   │   ├─ Energy (rested before camp)
│   │   ├─ Training items (Megaphones, Hammers)
│   │   ├─ Support card bonds at 80+ for friendship training
│   │   └─ Mood at Peak for +20% multiplier
│   ├─ Each turn: select highest-value training
│   │   ├─ Prioritize friendship training (rainbow)
│   │   ├─ Use training items for amplification
│   │   └─ Monitor Energy (higher costs at Lv5)
│   └─ Trackblazer-specific: shop refreshes on first summer turn; keep 100+ coins
│
├─ STEP 3: Camp Ends
│   ├─ Banner disappears
│   ├─ Facilities return to individual levels
│   └─ Normal training resumes
│
END: Summer camp window closed
```text

---

### Flow 8: Champions Meeting

```text
START: Champions Meeting event window opens
│
├─ STEP 1: League Selection (3–4 days before Round 1)
│   ├─ Choose: Graded League OR Open League
│   ├─ Eligibility check:
│   │   ├─ [Global] Open: Career Rank A+ or below
│   │   └─ [JP] Open: 育成ランク[UC] or below
│   ├─ Participation reward: Toughness 30 ×3 + Alarm Clock ×3
│   └─ CONFIRM: League locked once Round 1 opens
│
├─ STEP 2: Team Registration
│   ├─ Select 3 Veteran Umamusume (finished career runs)
│   ├─ Entry cost: 1 Entry Ticket or 30 Carats
│   ├─ Free entry model differs by server:
│   │   ├─ [JP]: 3 free tickets per day from event top
│   │   └─ [Global]: first daily entry free; edition-scoped tickets
│   └─ Up to 4 entries per day
│
├─ STEP 3: Round 1 (Preliminaries)
│   ├─ 5 races per entry
│   ├─ 3 trainers matched per race
│   ├─ 9 runners per race
│   ├─ Skill disable list active for this edition
│   ├─ Results: 3+ wins → Group A; <3 → Group B
│   └─ Per-entry rewards scale with wins
│
├─ STEP 4: Round 2 (Preliminaries)
│   ├─ Same structure as Round 1
│   ├─ Group A/B split
│   ├─ 0 wins → eliminated
│   └─ 3+ wins → Group A of Final
│
├─ STEP 5: Final Registration + Matching
│   ├─ Registration window (specific hours)
│   ├─ Matching window
│   └─ Final race: one race between three trainers
│
├─ STEP 6: Results
│   ├─ Rewards by league, group, placement
│   ├─ Event-exclusive titles (gain star when re-earned)
│   ├─ [JP] daily missions pay 栄誉のメダリオン (Honor Medallion)
│   └─ [Global] daily missions pay "<Cup> Entry Tickets" ×2
│
END: Champions Meeting edition closed
```text

---

### Flow 9: Gacha / Scout Pull

```text
START: User navigates to Scout/Gacha screen
│
├─ STEP 1: Banner Selection
│   ├─ Active banners displayed (UM-GAC-001)
│   ├─ User selects target banner
│   ├─ Featured unit, rates, exchange info shown
│   └─ Timer shows remaining window
│
├─ STEP 2: Pull Execution
│   ├─ OPTION A: Single pull (150 ジュエル/Carats or ticket)
│   ├─ OPTION B: 10-pull (1500 ジュエル/Carats)
│   ├─ OPTION C: Paid 10-pull (1500 有償ジュエル)
│   │   └─ May carry guaranteed ★3/SSR on slot 10 (banner-specific)
│   ├─ OPTION D: Daily limited single (50 有償ジュエル, [JP])
│   └─ User confirms pull
│
├─ STEP 3: Pull Animation (UM-GAC-002)
│   ├─ Rarity pre-reveal indicators
│   ├─ Character/card reveal
│   ├─ NEW unit: "NEW!" badge + celebration
│   └─ Duplicate: converts to exchange points / Star Pieces
│
├─ STEP 4: Post-Pull
│   ├─ Exchange Points incremented (+1 per pull)
│   ├─ IF Exchange Points ≥ 200:
│   │   ├─ "Exchange" button active
│   │   ├─ User can redeem for featured unit
│   │   └─ Exchange Points consumed
│   ├─ IF banner closes with < 200 Points:
│   │   └─ Remaining Points auto-convert to Clovers
│   └─ "Pull Again" / "Go to Deck" options
│
END: Pull resolved
```text

---

### Flow 10: Scenario-Specific Flows

#### 10A: Unity Cup — Team Race Cycle

```text
START: Every 6 months of career
│
├─ STEP 1: Team Formation
│   ├─ Team auto-formed from: support cards, story characters, random recruits
│   ├─ User assigns members to 5 categories (Sprint, Mile, Medium, Long, Dirt)
│   └─ Each group of 3 per category
│
├─ STEP 2: Opponent Selection
│   ├─ Choose from 3 NPC teams (strongest → weakest)
│   ├─ League rank gain/loss previewed
│   ├─ Tazuna provides win probability per discipline
│   └─ Recommend: at least 3 circles total for safety
│
├─ STEP 3: Team Race
│   ├─ 5 races run automatically
│   ├─ Main race (trainee's) viewable in full
│   ├─ Other races: results only
│   ├─ Alarm Clock usable (up to 3) if losing
│   └─ Win = 3+ of 5 races
│
├─ STEP 4: Results
│   ├─ Team rank updated
│   ├─ New random characters join team
│   ├─ Facility levels recalculated based on team rank
│   └─ After 4 team races: Unity Cup Finals vs Team Zenith
│
END: Team race cycle complete
```text

#### 10B: Trackblazer — Special Shop Economy

```text
START: After Debut Race
│
├─ STEP 1: Special Shop Unlocks
│   ├─ Shop refreshes every 6 turns
│   ├─ Races add additional items (last 3 turns)
│   └─ Sales events: 10–20% discount
│
├─ STEP 2: Earn Shop Coins
│   ├─ Race placement determines coins:
│   │   ├─ 1st: 100 coins
│   │   ├─ 2nd–3rd: 60 coins
│   │   ├─ 4th–5th: 30 coins
│   │   └─ 6th+: 0 coins
│   └─ All race grades award coins
│
├─ STEP 3: Purchase Items
│   ├─ Priority items (Must Buy):
│   │   ├─ +15 Stat (30 coins)
│   │   ├─ +7 Stat (15 coins)
│   │   ├─ +2 Mood (55 coins)
│   │   ├─ +1 Mood (30 coins)
│   │   ├─ +65 Energy (75 coins)
│   │   ├─ +40 Energy (55 coins)
│   │   ├─ +35% Race Bonus (40 coins)
│   │   ├─ +60% Training Bonus for 2 turns (70 coins)
│   │   ├─ Card Shuffle (20 coins)
│   │   ├─ +50% Specific Training Bonus (50 coins)
│   │   └─ Cannot Fail Training for 1 turn (40 coins)
│   ├─ Skip items: +8 Max Energy (55), +20% Training Bonus 4 turns (40)
│   └─ Max 5 copies of any item
│
├─ STEP 4: Twinkle Star Climax (Finale)
│   ├─ 3 races replace URA Finale
│   ├─ Placement → Victory Points (1st=10, 2nd=8, 3rd=6, etc.)
│   ├─ More total points than rivals = win
│   ├─ Winning grants "Radiant Star" skill hints
│   ├─ Save 3 Golden Hammers for these races
│   └─ Save ~150 coins for final shop
│
END: Trackblazer run complete
```text

#### 10C: Grand Masters — Three Goddesses System

```text
START: Turn 3 of Grand Masters scenario
│
├─ STEP 1: Knowledge Fragment Collection
│   ├─ Training, resting, outings, racing all generate Fragments
│   ├─ Fragments auto-combine:
│   │   ├─ 2 Fragments → 1 Knowledge Crystal
│   │   ├─ 2 Crystals → 1 Merged Crystal
│   │   └─ 2 Merged Crystals (8 Fragments) → 1 Goddess's Wisdom
│   └─ Three types of Wisdom: one per Goddess
│
├─ STEP 2: Activate Goddess's Wisdom
│   ├─ Manual trigger via "Obtain Wisdom" button
│   ├─ Cannot obtain more Fragments until next turn
│   ├─ Permanently levels up that Goddess AI (up to Lv5)
│   └─ Grants one-turn-only activation bonus
│
├─ STEP 3: Goddess Effects
│   ├─ GODOLPHIN BARB (Blue):
│   │   ├─ Passive: Hint Rate Up (+20% to +35%), After-Training Event Chance Up, Training Bonus (+5% to +15%)
│   │   └─ Activation: Every card on facility gives skill hint + extra stats/SP
│   │
│   ├─ DARLEY ARABIAN (Red):
│   │   ├─ Passive: Energy Discount (10% to 23%), Training Bonus (+5% to +15%)
│   │   └─ Activation: +50 Energy, max Mood, all facilities exceed Lv5 (red border), +35% race stats
│   │
│   └─ BYERLEY TURK (Yellow):
│       ├─ Passive: Support Event Effect Up (10% to 25%), Chain Event Chance Up (+20 to +90), Training Bonus (+5% to +15%)
│       └─ Activation: ALL cards trigger Friendship Training regardless of bond/type/facility
│
END: Goddess activated, bonuses applied
```text

---

## PART 3: HEURISTIC EVALUATION

### Methodology

This evaluation assesses *Umamusume: Pretty Derby* against Nielsen's 10 Usability Heuristics, supplemented by 5 game-specific heuristics. Each heuristic is rated 0–10, with specific mechanical examples, identified issues, severity ratings, and actionable recommendations. Severity scale: 0 (cosmetic) → 4 (catastrophic).

---

### H1: Visibility of System Status

**Rating: 7/10**

**What the game does well:**

- Training gain previews are shown before every commit: each facility tab displays predicted stat gains, energy cost, and participant count. The user always knows "what will happen if I tap this."
- Energy meter has clear threshold markers at 50 (caution) and 25 (critical), with color shifts and pulse animations.
- Bond gauge segments (4 visible) turn orange at 80, clearly signaling friendship training availability.
- Race viewer shows phase progression with section counter (e.g., "Section 12/24") and percentage through race.
- Turn counter and calendar date are always visible during training.

**Issues identified:**

| Issue                                                                                                   | Severity   | Evidence                                                                                          |
| ------------------------------------------------------------------------------------------------------- | ---------- | ------------------------------------------------------------------------------------------------- |
| Training failure probability is not shown as a percentage; only implied by energy level                 | 2          | Game8 states "failure risk changes character" at 50 Energy but no exact percentage is displayed   |
| Skill activation rate (Wit-gated) is never quantified to the user                                       | 2          | Wit governs "skill activation rate" per Game8 (2026-09-24) but no percentage is shown             |
| Compatibility grades (△/○/◎) between ancestors are shown but the underlying numerical value is hidden   | 1          | User cannot see exact compatibility score, only the grade                                         |
| Whether Wit training can fail is unresolved even across sources                                         | 2          | Conflict Log row 2: GameWith lists Wit failure; Game8 implies exemption. No in-game clarity       |
| Scenario stat caps are shown in scenario selection but not during the run itself                        | 1          | User must exit the run to check which cap applies                                                 |

**Recommendations:**

1. Display failure probability as a percentage on the training detail panel when Energy < 50.
2. Add a "Skill Activation Rate" stat summary to the pre-race screen, derived from Wit value.
3. Show numerical compatibility score alongside the △/○/◎ grade in the ancestor circle.
4. Add a persistent "Scenario Cap" indicator in the training stat panel (e.g., "Cap: 1400" next to each stat).
5. Clarify Wit training failure status with an in-game tooltip: "Wit training has very low failure risk."

---

### H2: Match Between System and Real World

**Rating: 6/10**

**What the game does well:**

- Stats map to intuitive racing concepts: Speed = fast, Stamina = endurance, Power = acceleration, Guts = tenacity, Wit = intelligence/skill use.
- Running strategies have clear real-world analogs: Front Runner (leads), Pace Chaser (mid-pack), Late Surger (moves late), End Closer (sprints from back).
- Weather and ground conditions mirror real racing: Rain → Soft/Heavy ground → slower times.
- Distance bands (Sprint, Mile, Medium, Long) are standard racing terminology.

**Issues identified:**

| Issue                                                                                                      | Severity   | Evidence                                                                                                                                                                   |
| ---------------------------------------------------------------------------------------------------------- | ---------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Server terminology divergence creates confusion                                                            | 3          | "Scouts" vs "ガチャ", "Pal" vs "友人", "Veteran Umamusume" vs "殿堂入り", "Spark" vs "因子" — same concepts, different words across servers                                |
| Champions Meeting naming is completely different between servers                                           | 3          | JP uses category tags (SPRINT/MILE/CLASSIC/LONG/DIRT); Global uses zodiac cups (Scorpio Cup). These are NOT equivalent series; Global runs the series JP retired in 2023   |
| "Wit" vs "Wisdom" vs "Intelligence" inconsistency                                                          | 2          | Export key is `intelligence`; some client strings say "Wisdom Bonus"; player-facing text uses "Wit" (Conflict Log row 4)                                                   |
| The fifth stat's display name varies even within Global assets                                             | 2          | Conflict Log row 4: local effect strings alternate between "Wisdom Bonus" and "Intelligence Limit Up"                                                                      |
| "Trackblazer" vs "Twinkle Star Climax" naming conflict                                                     | 2          | Export says "Trackblazer"; Global mission text says "Twinkle Star Climax"; no official title confirmed (Conflict Log row 31)                                               |
| Running style labels: "Runner/Leader/Betweener/Chaser" circulate in community but are NOT client strings   | 2          | Client strings are "Front Runner/Pace Chaser/Late Surger/End Closer"; community labels appear nowhere in client text                                                       |

**Recommendations:**

1. Create a unified terminology map and enforce it across all UI surfaces. Never mix JP and Global terms in a single screen.
2. For Champions Meeting, always display the target race conditions (distance, surface, course) alongside the edition name, so users can identify the actual race regardless of naming convention.
3. Standardize on "Wit" for all player-facing text; use "intelligence" only in internal data keys.
4. Resolve the Trackblazer/Twinkle Star Climax naming by displaying both: "Trackblazer: Twinkle Star Climax" until an official Global name is confirmed.
5. Purge community labels (Runner/Leader/Betweener/Chaser) from all official documentation and UI; use only client strings.

---

### H3: User Control and Freedom

**Rating: 7/10**

**What the game does well:**

- Training detail panel allows canceling before commit.
- Race viewer supports speed controls (1×, 2×, skip) and segment replay.
- Skill shop allows browsing without committing SP.
- Ancestor circle allows changing any slot before confirming.
- Deck building allows swapping cards before locking.

**Issues identified:**

| Issue                                                                                                     | Severity   | Evidence                                                                                                                           |
| --------------------------------------------------------------------------------------------------------- | ---------- | ---------------------------------------------------------------------------------------------------------------------------------- |
| No undo after training commit                                                                             | 3          | Once "Train" is tapped and resolved, there is no way to revert the turn. A failure at low Energy permanently applies the penalty   |
| No undo after skill purchase                                                                              | 2          | SP spent on a skill cannot be refunded. User may accidentally buy the wrong skill                                                  |
| No undo after gacha pull                                                                                  | 3          | Carats/jewels spent are non-refundable. Exchange points are earned but the pull itself is irreversible                             |
| Idle training mode (自主トレ育成) cannot be interrupted or manually controlled once started               | 2          | Kamigame: "You cannot take manual control partway through." The 50-minute run plays out regardless                                 |
| Inheritance factors are rolled randomly; user cannot choose which factors a finished character produces   | 2          | "The player does not pick a fixed list" — factors follow final stats, aptitudes, and skills probabilistically                      |
| Ancestor selection is locked once the run begins                                                          | 1          | No mid-run ancestor swap                                                                                                           |

**Recommendations:**

1. Add a "confirm" dialog before training with Energy < 25, showing explicit failure risk: "Training at this Energy level has a high chance of failure. Proceed?"
2. Add a 5-second "undo" window after skill purchase, similar to mobile app "undo send" patterns.
3. For idle training, add a "Cancel and forfeit" option that returns the user to the main menu without completing the 50-minute run.
4. Show a "Factor Preview" on the post-run screen that estimates likely factor outcomes based on final stats, so the user knows what they'll get before the run is finalized.
5. Add a "Deck Preview" step before run confirmation that simulates the first 3 turns with the chosen deck, letting the user test synergy.

---

### H4: Consistency and Standards

**Rating: 6/10**

**What the game does well:**

- Stat colors are consistent across all surfaces (Speed = red, Stamina = blue, Power = orange, Guts = yellow, Wit = green).
- Training facility layout is consistent: 5 tabs, fixed order, same interaction pattern.
- Race phases always use the same 4-phase structure across all 138 courses.
- Bond gauge behavior is consistent: 0–100 scale, orange at 80, +5 per joint training.

**Issues identified:**

| Issue                                                                                              | Severity   | Evidence                                                                                                                                                                                 |
| -------------------------------------------------------------------------------------------------- | ---------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Facility leveling rule differs by scenario without clear indication                                | 2          | URA/Trackblazer: levels up every 4 uses. Unity Cup: tied to team rank. Grand Masters: can exceed Lv5 via Goddess buff. User must know which scenario they're in to understand leveling   |
| Support card upgrade model differs by server                                                       | 3          | JP removed card levels and Support Pt on 2025-10-07; Global retains the original model. Same card, different progression systems                                                         |
| Champions Meeting entry economy differs by server                                                  | 2          | JP: 3 free tickets/day from event top, generic Entry Ticket. Global: first daily entry free, edition-scoped tickets ("Scorpio Cup Entry Ticket")                                         |
| Open League rank ceiling differs by server                                                         | 2          | JP: up to 育成ランク[UC]. Global: up to Career Rank A+. Same bracket name, different ceiling                                                                                             |
| Hint level cost reduction is documented at ~10% per level but exact percentages are ❌ UNVERIFIED  | 1          | Conflict Log row 16: single source (Game8 JP) for the 30% at Lv3 figure                                                                                                                  |
| Distance band boundary at 1400m is contested                                                       | 2          | Export codes 1400m as Mile (distance_type==2); Umamusume Wiki Career Mode lists it as Sprint/Short. Engine disagrees with wiki                                                           |

**Recommendations:**

1. Add a "Scenario Rules" tooltip to the training screen that explicitly states how facility leveling works in the current scenario.
2. For server-specific differences, add a "Server Info" panel in settings that lists all mechanical differences between JP and Global.
3. Standardize distance band boundaries in the UI: always show both the meter value AND the band label, and use the engine's classification (1400m = Mile) as the source of truth.
4. Publish exact hint level discount percentages in the skill shop tooltip: "Each Hint Level reduces cost by 10%. Maximum 30% at Lv3."
5. Add a "What's Different" indicator when a user switches between scenarios, highlighting rule changes.

---

### H5: Error Prevention

**Rating: 7/10**

**What the game does well:**

- Training gain preview prevents surprise: user sees exact expected gains before committing.
- Energy threshold at 50 serves as a natural warning boundary.
- Friendship training requires two conditions (bond ≥ 80 + matching facility), preventing accidental triggers.
- Gacha exchange point system prevents total loss: even bad pulls accumulate toward a guaranteed exchange at 200 points.
- Alarm Clock item allows retrying failed mandatory objectives.

**Issues identified:**

| Issue                                                                    | Severity   | Evidence                                                                                                                           |
| ------------------------------------------------------------------------ | ---------- | ---------------------------------------------------------------------------------------------------------------------------------- |
| No confirmation dialog before training at critically low Energy          | 3          | User can tap "Train" at 5 Energy and suffer a failure penalty without any additional warning beyond the color change               |
| No confirmation before spending large amounts of SP on a single skill    | 1          | A 340 SP skill (e.g., Arc Line Maestro) can be purchased with a single tap                                                         |
| No warning when equipping a deck that violates scenario requirements     | 2          | E.g., Unity Cup requires 4+ distinct types for bonus training; no explicit warning if user brings 3 types                          |
| Gacha exchange points expire silently at banner close                    | 2          | Unspent points below 200 auto-convert to Clovers; no proactive warning before banner closes                                        |
| Transfer Requests permanently delete the transferred Veteran Umamusume   | 3          | "A transferred Veteran Umamusume disappears and cannot be recovered." No confirmation step documented beyond the transfer itself   |

**Recommendations:**

1. Add a modal confirmation when training with Energy < 25: "Warning: Training at this Energy level has a very high failure risk. Are you sure?"
2. Add a confirmation dialog for any skill purchase exceeding 200 SP.
3. Add a deck validation step in scenario setup that warns: "This scenario requires 4+ distinct card types for bonus training. Your current deck has 3."
4. Send a push notification 24 hours before banner close if the user has 150+ exchange points but hasn't reached 200.
5. For Transfer Requests, add a two-step confirmation: first showing the character being transferred, then requiring the user to type the character's name to confirm deletion.

---

### H6: Recognition Rather Than Recall

**Rating: 7/10**

**What the game does well:**

- Support card avatars appear directly on training tiles, so the user doesn't need to remember which cards are where.
- Bond gauge is always visible on card avatars, eliminating the need to recall bond levels.
- Skill hints show the discounted cost directly, so the user doesn't need to calculate the discount.
- Race condition tags (weather, ground, distance) are displayed as visual icons with text labels.
- Scenario objectives are shown in a persistent tracker, not buried in menus.

**Issues identified:**

| Issue                                                                                                         | Severity   | Evidence                                                                                                       |
| ------------------------------------------------------------------------------------------------------------- | ---------- | -------------------------------------------------------------------------------------------------------------- |
| Factor farming requires memorizing optimal stat thresholds                                                    | 2          | User must recall: "Speed > 1100 for 3★ chance" and "Aptitude at A for 100% roll." No in-game reference table   |
| Skill activation conditions are text-heavy and require parsing                                                | 2          | Conditions like `running_style==1&slope==1&accumulatetime>=10` are translated to prose but remain complex      |
| Compatibility grades require knowledge of what △/○/◎ mean mechanically                                        | 1          | No tooltip explains that ◎ increases both trigger chance and payout magnitude                                  |
| Scenario-specific mechanics (Spirit Burst, Knowledge Fragments) are taught via a one-time tutorial event      | 2          | After the tutorial, there is no persistent reference for how the mechanic works                                |
| Summer camp timing (first half July to second half August, 4 turns) is not shown on the calendar in advance   | 1          | User must know from external guides when camp is coming                                                        |

**Recommendations:**

1. Add a "Factor Guide" reference screen accessible from the inheritance setup, showing star rating odds per stat value.
2. Parse skill activation conditions into visual tags: [Phase: Final Leg] [Style: Front Runner] [Terrain: Uphill] instead of prose sentences.
3. Add tooltips to compatibility grades: "◎ = Highest compatibility. Increases factor trigger chance and payout size."
4. Add a "Scenario Guide" button to the training screen that opens a reference panel for the current scenario's unique mechanics.
5. Show upcoming summer camp windows on the calendar with a "Prepare" indicator 4 turns before camp starts.

---

### H7: Flexibility and Efficiency of Use

**Rating: 8/10**

**What the game does well:**

- Race viewer supports 1×, 2×, and skip speeds.
- Skill cut-ins can be set to "Show all" / "Show rare+ only" / "Skip all."
- `[JP]` スキルセット feature allows bulk SP allocation across priority groups.
- `[JP]` 自主トレ育成 provides an idle mode for resource farming without active play.
- Friend rental allows borrowing a high-factor ancestor without building one.
- Deck templates and four-type rules provide strategic shortcuts.

**Issues identified:**

| Issue                                                              | Severity   | Evidence                                                                          |
| ------------------------------------------------------------------ | ---------- | --------------------------------------------------------------------------------- |
| No auto-training or quick-resolve for standard training turns      | 2          | Every turn requires manual selection, even when the optimal choice is obvious     |
| No batch skill learning                                            | 1          | Each skill must be individually selected and confirmed (except JP's スキルセット) |
| Race replay does not allow changing speed mid-race                 | 1          | Speed is set before playback begins                                               |
| No deck save/load templates for quick swapping between scenarios   | 1          | User must manually rebuild their 6-card deck for each scenario                    |

**Recommendations:**

1. Add an "Auto-Train" toggle that automatically selects the highest-gain training each turn, with a "confirm before friendship training" option.
2. Add multi-select in the skill shop: checkbox selection + "Learn All" button.
3. Allow speed changes during race playback.
4. Add "Deck Presets" that save named 6-card configurations for quick loading.

---

### H8: Aesthetic and Minimalist Design

**Rating: 6/10**

**What the game does well:**

- Training screen has a clear visual hierarchy: stat panel (top) → facility tabs (middle) → action buttons (bottom).
- Stat colors are distinct and consistently applied.
- Race phase indicator uses a clean 4-segment bar.
- Bond gauge uses a simple 4-segment design with clear color transitions.

**Issues identified:**

| Issue                                                                                                                                                            | Severity   | Evidence                                                                                                                                                |
| ---------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------- | ------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Training detail panel can become visually overwhelming with 6 support cards, bond gauges, multiplier breakdowns, and event triggers all visible simultaneously   | 2          | A full friendship training with 3 qualifying cards, 2 events, and a scenario trigger produces a dense modal                                             |
| Skill shop has 10+ category filters, creating a cluttered filter bar                                                                                             | 1          | Sprint, Mile, Medium, Long, Front Runner, Pace Chaser, Late Surger, End Closer, Corner, Straight, Uphill, Downhill, Weather, Ground, Time, Popularity   |
| Race condition display shows 7 fields (surface, distance, turn, season, weather, ground, time) which can overwhelm pre-race                                      | 1          | Most fields are irrelevant for a given build                                                                                                            |
| Ancestor circle with 6 nodes, factor tags, star ratings, and compatibility grades is information-dense                                                           | 2          | New users may struggle to parse the diagram                                                                                                             |

**Recommendations:**

1. Use progressive disclosure in the training detail panel: show primary gains first, tap "Details" for multiplier breakdown.
2. Group skill filters into collapsible categories: "Distance" / "Strategy" / "Terrain" / "Condition" instead of 16 flat tabs.
3. Show only relevant race conditions based on the user's build: if no weather skills are equipped, collapse the weather field.
4. Add a "Simplified View" toggle for the ancestor circle that hides factor tags and shows only character portraits and compatibility grades.

---

### H9: Help Users Recognize, Diagnose, and Recover from Errors

**Rating: 5/10**

**What the game does well:**

- Training failure modal clearly states which penalty was applied (Energy loss, Mood drop, or Injury).
- Alarm Clock provides a recovery path for failed mandatory objectives.
- Pal cards with Failure Protection can suppress penalties, giving a safety net.

**Issues identified:**

| Issue                                                                           | Severity   | Evidence                                                                                                                            |
| ------------------------------------------------------------------------------- | ---------- | ----------------------------------------------------------------------------------------------------------------------------------- |
| Training failure does not explain WHY it failed                                 | 3          | The modal shows the penalty but not the failure probability that led to it. User doesn't learn "I should have rested"               |
| Skill activation failure during a race is silent                                | 2          | If a skill doesn't trigger, there's no indication of why (wrong phase, wrong position, wrong running style). User cannot diagnose   |
| Inheritance "no trigger" events give no explanation                             | 1          | If no factor fires at April of Classic Year, the user sees "No inspiration" with no explanation of probability                      |
| Gacha exchange point expiry is not warned proactively                           | 2          | Points convert to Clovers at banner close with no advance notification                                                              |
| Transfer Requests delete characters permanently with limited recovery options   | 3          | "A transferred Veteran Umamusume disappears and cannot be recovered." No undo, no confirmation beyond the transfer action           |

**Recommendations:**

1. Add failure cause to the training failure modal: "Training failed due to low Energy (12/100). Consider resting before training."
2. Add a post-race "Skill Report" that lists each equipped skill and whether it activated, with the reason if it didn't: "Did not activate: requires Final Leg phase" or "Did not activate: requires Front Runner strategy."
3. Add probability context to inheritance events: "No factor triggered this time. Compatibility grade ○ gives approximately X% trigger chance."
4. Send a notification 48 hours before banner close if user has 100+ exchange points.
5. For Transfer Requests, add a 24-hour "pending" state before the character is permanently deleted, allowing cancellation.

---

### H10: Help and Documentation

**Rating: 4/10**

**What the game does well:**

- In-game tutorial covers basic training, racing, and support card mechanics.
- Scenario-specific tutorials (e.g., Tazuna's Unity Cup tutorial event) introduce unique mechanics narratively.
- Skill descriptions include activation condition text.

**Issues identified:**

| Issue                                                                            | Severity   | Evidence                                                                                                                          |
| -------------------------------------------------------------------------------- | ---------- | --------------------------------------------------------------------------------------------------------------------------------- |
| No in-game reference for the training gain formula                               | 3          | The formula `(base + bonus) × growth × mood × friendship × participants` is documented only on external wikis (Game8, Kamigame)   |
| No in-game guide for factor farming strategy                                     | 2          | Optimal thresholds (stat > 1100 for 3★, grandparent-first ordering) are community knowledge                                       |
| No in-game explanation of compatibility calculation                              | 2          | How △/○/◎ are derived (shared relationships, similar aptitudes, shared G1 wins) is not explained                                  |
| Scenario-specific mechanics lack persistent reference after the tutorial event   | 2          | Once Tazuna's tutorial passes, there's no way to re-read Unity Cup mechanics                                                      |
| Server-specific differences are not documented in-game                           | 2          | JP vs Global terminology, league ceilings, entry economy differences are only in external guides                                  |
| Rate tables for gacha are only available in-app, not on web                      | 1          | Both servers route to in-app [ガチャ詳細] / Scout Rates tab; no web publication                                                   |

**Recommendations:**

1. Add a "Training Guide" section to settings that explains the gain formula with visual examples.
2. Add a "Factor Guide" with stat threshold tables and farming strategy tips.
3. Add a "Compatibility Guide" explaining how grades are calculated.
4. Add a "Scenario Reference" button that opens a persistent guide for the current scenario's mechanics.
5. Add a "Server Info" page documenting all JP/Global differences.
6. Publish gacha rate tables on the official website in addition to the in-app tab.

---

### Game-Specific Heuristics

#### GH1: Feedback Loop Quality

**Rating: 8/10**

**Strengths:**

- Every training action produces immediate, visible stat gains with animated count-ups.
- Friendship training has a distinct rainbow visual + amplified numbers, making high-value moments feel rewarding.
- Skill activation cut-ins provide dramatic feedback during races.
- Bond gauge filling (+5 per session) gives incremental progress feedback.
- Factor inheritance events at three fixed moments create anticipation cycles.

**Weaknesses:**

- Training failure feedback is punitive but not instructive (see H9).
- Skill non-activation during races provides zero feedback.
- The long-term factor farming loop has delayed gratification: many runs before a 3★ factor appears.

**Recommendation:** Add a "progress toward next 3★ factor" tracker that estimates probability based on current stat trajectory.

---

#### GH2: Reward Cadence

**Rating: 8/10**

**Strengths:**

- Multi-tier reward schedule: per-turn (stats), per-race (fans, SP), per-event (hints, items), per-run (factors, titles), long-term (factor inheritance, team rank).
- Summer camp creates a 4-turn power spike that rewards preparation.
- Champions Meeting provides per-entry rewards even for losses.
- Legend Race pays for entry even without a win.

**Weaknesses:**

- Gacha rewards are highly variable (79% R-rate creates long dry spells).
- Factor farming can require 10+ runs before a desired 3★ factor appears.
- Training Pass reward quantities are ❌ UNVERIFIED and published only in-app.

**Recommendation:** Add a "pity" indicator for factor farming: "You have completed 8 runs without a 3★ Speed factor. Estimated probability increases with higher final stats."

---

#### GH3: Cognitive Load Management

**Rating: 6/10**

**Strengths:**

- Predicted gain previews reduce decision anxiety.
- Discrete mood states (5 levels) are easier to parse than a continuous bar.
- Color-coded factor tags enable rapid category recognition.
- Scenario selection separates rule complexity into distinct modes.

**Weaknesses:**

- Pre-career setup requires 6 sequential decisions (scenario, character, ancestors, deck, abilities, confirm) before the first turn.
- Training gain formula has 6 multiplicative terms, none of which are surfaced to the user.
- Skill activation conditions combine multiple variables (phase, position, running style, terrain, weather) that must all be satisfied.
- Ancestor circle with 6 nodes × multiple factors × star ratings × compatibility grades is a high-density information display.

**Recommendation:** Implement progressive disclosure throughout: show the simple version first (total gain), with a "Details" expandable for the formula breakdown. For skill conditions, use visual tag chips instead of prose.

---

#### GH4: Information Architecture

**Rating: 7/10**

**Strengths:**

- Training screen has clear spatial hierarchy: stats top, actions middle, alternatives bottom.
- Race phases are temporally ordered and visually segmented.
- Support cards are organized by type with consistent color coding.
- Scenario selection separates fundamentally different rule sets.

**Weaknesses:**

- Skill shop has too many flat filters (16+ categories).
- Terminology differences between JP and Global create information fragmentation.
- Champions Meeting naming (zodiac vs. category tag) makes cross-server information retrieval impossible without knowing the mapping.
- Factor categories (Blue/Pink/Green/White) use color as primary identifier, which fails for color-blind users.

**Recommendation:** Restructure skill filters into a two-level hierarchy (Category → Subcategory). Add shape coding to factor tags (circle = stat, diamond = aptitude, star = unique, square = skill). Create a unified cross-server event index keyed on `resource_id` rather than event name.

---

#### GH5: Progressive Disclosure

**Rating: 6/10**

**Strengths:**

- New scenarios introduce mechanics via narrative tutorial events (e.g., Tazuna's Unity Cup tutorial).
- Skill hints reduce SP cost, gradually revealing the skill system's depth.
- Factor system starts simple (Blue sparks at career start) and reveals complexity mid-run (White sparks, golden events).

**Weaknesses:**

- All five training facilities are available from turn 1, even though a new player doesn't understand the stat system yet.
- Skill shop shows all available skills immediately, including phase-gated and style-gated skills the user can't yet contextualize.
- Inheritance setup requires understanding factors, compatibility, and star ratings before the first run.
- Summer camp mechanics are not previewed; the user encounters forced Lv5 without prior warning.

**Recommendation:** Lock advanced skill filters behind a "Show Advanced" toggle. Add a "First Run" mode that simplifies ancestor selection to a recommended preset. Show a "Summer Camp in 4 turns" countdown on the calendar starting 8 turns before camp.

---

### Heuristic Evaluation Summary

| Heuristic                                 | Rating   | Top Issue                                        | Severity   |
| ----------------------------------------- | -------- | ------------------------------------------------ | ---------- |
| H1: Visibility of System Status           | 7/10     | No failure probability percentage shown          | 2          |
| H2: Match Between System and Real World   | 6/10     | Server terminology divergence                    | 3          |
| H3: User Control and Freedom              | 7/10     | No undo after training commit                    | 3          |
| H4: Consistency and Standards             | 6/10     | Facility leveling differs by scenario silently   | 2          |
| H5: Error Prevention                      | 7/10     | No confirmation at critically low Energy         | 3          |
| H6: Recognition Rather Than Recall        | 7/10     | Factor thresholds require memorization           | 2          |
| H7: Flexibility and Efficiency of Use     | 8/10     | No auto-training option                          | 2          |
| H8: Aesthetic and Minimalist Design       | 6/10     | Training detail panel visual density             | 2          |
| H9: Error Recognition and Recovery        | 5/10     | Training failure doesn't explain cause           | 3          |
| H10: Help and Documentation               | 4/10     | Gain formula not documented in-game              | 3          |
| GH1: Feedback Loop Quality                | 8/10     | Skill non-activation is silent                   | 2          |
| GH2: Reward Cadence                       | 8/10     | Factor farming has long dry spells               | 2          |
| GH3: Cognitive Load Management            | 6/10     | 6-term gain formula hidden from user             | 2          |
| GH4: Information Architecture             | 7/10     | Too many flat skill filters                      | 1          |
| GH5: Progressive Disclosure               | 6/10     | All mechanics exposed from turn 1                | 2          |

**Overall Score: 6.6/10**

**Top 3 Systemic Issues:**

1. **Error feedback is punitive but not instructive** (H9, GH1): Training failures and skill non-activations tell the user *what* happened but not *why*, preventing learning.
2. **Server terminology fragmentation** (H2, H4): JP and Global use different words for identical concepts, different rules for identical features, and different naming for identical events, creating a fractured information landscape.
3. **Hidden formula complexity** (H1, GH3, H10): The training gain formula, compatibility calculation, factor star odds, and skill activation rates are all mechanically rich but surfaced to the user as opaque outcomes rather than transparent systems.

---

*End of document. All numerical thresholds, terminology mappings, and mechanical rules are sourced from the Umamusume: Pretty Derby Source-Cited Reference Guide (compiled 2026-09-27) and the skill registry. Server-specific data is tagged `[JP]`, `[Global]`, or `[Both]` throughout. Unverified claims are marked ❌ UNVERIFIED and should not be treated as confirmed.*

---

## Part 2: UX Behavior Specification: Umamusume Trainer Companion

> **Reviewed, largely sound, not merged — 2026-09-27.** Audit: `docs/design-research/FRONTEND-SPEC-DIVERGENCE.md` §6.
> Three corrections are load-bearing: preferences persist to **SQLite**, not `localStorage` (owner ruling, PRD §6.12;
> see `CONSTRAINTS.md` D-104); the cap-stack example's `+0 breakthrough` is the value DESIGN.md §6.22 forbids
> displaying; and "Run List = default landing" contradicts root DESIGN §4.1, where the catalog index is the desk's
> front page. The universal 4-step turn flow also has to become scenario-composed (`config/scenarios.php` `steps`).
> The input taxonomy (§3) and run-lifecycle state machine (§4) are worth adopting as structure.

Synthesized from `DESIGN.md`, `CONSTRAINTS.md`, `PRD.md`, the ADRs, and the scenario reference files. This is the behavioral contract — what the app does, when, and why.

---

### 1. Global App Behavior (Outside Career Run)

#### 1.1 App Shell & Navigation

The app is a **desktop-browser-only** local tool. No auth, no accounts, no server, no sync (PRD §6.1). Everything lives in the browser's storage.

**Navigation structure:**

| Surface             | Purpose                                         | Entry point        |
| ------------------- | ----------------------------------------------- | ------------------ |
| Run List            | All recorded career runs, filterable/sortable   | Default landing    |
| Run Detail          | One run's full timeline, stats, skills          | Click a run card   |
| Guided Turn Input   | Log a new turn                                  | From Run Detail    |
| Skill Search        | Find skills by normalized key                   | Global nav         |
| Catalog             | Umamusume roster with aptitudes                 | Global nav         |
| Settings            | Theme, display timezone, config                 | Global nav         |

**The shell must:**

- Apply the selected theme **before first paint** (inline `<script>` in `<head>`, no flash of wrong theme — CONSTRAINTS D-104)
- Default to **light theme** (the client is a high-key interface — DESIGN §3.7)
- Support dark theme as a token override, not a fork (D-101)
- Persist theme choice to `localStorage`, initialize from `prefers-color-scheme` (D-104)
- Display all dates through `config('uma.display_timezone')` (D-34)

#### 1.2 Run List (Screen C)

**Behavior:**

- Renders as **cards, not a table** (DESIGN §6.20)
- Each card carries: trainee name, scenario, status pill, turn-depth chip, micro-grade strip, skill plan tally
- **Sort and filter** are `label`-weight controls in the panel header — never a toolbar that outshouts the data (D-61)
- Cards are clickable → navigate to Run Detail

**Inputs:**

- Sort dropdown (date, turn depth, trainee name)
- Filter chips (scenario, status)
- No bulk actions, no selection mode — this is a browser, not a manager

**States:**

- Empty: "No runs yet. Start your first career." with one primary button
- Populated: card grid
- Error: impossible for local data; if storage fails, show error state with retry

#### 1.3 Skill Search (Screen D)

**Behavior:**

- Single search field, autocomplete on **normalized keys** (FR-D-2)
- Matching is on `match_key`, never on display strings (CONSTRAINTS floor)
- A Japanese query may return an English row — the result must show **which field matched** (D-63)
- Results are **skill rows** (the client's own object shape — DESIGN §6.11)

**Inputs:**

- Search field with debounce
- No filters in Phase 1 (type/distance filters are future scope)

**States:**

- Empty search: inviting prompt
- No results: "Skill not found in catalog" + offer to check the review queue (US-5)
- Results: list of skill rows

#### 1.4 Catalog

**Behavior:**

- Read-only roster of Umamusume with aptitudes
- Sourced from `uma:fetch` pipeline (FR-A) — never hand-seeded (ADR-0004)
- Shows: name, aptitude letters (10-element array), debut dates, server status

**Inputs:**

- Search/filter by name
- No editing — catalog is engine-owned reference data

**States:**

- Loading: show last-fetched time + progress mark (stale-while-revalidate per ARCHITECTURE §6)
- Populated: list/grid of units
- Error: failed fetch must never blank the catalog (NFR-2)

#### 1.5 Settings

**Available toggles:**

| Setting                    | Options                 | Default              | Persistence      |
| -------------------------- | ----------------------- | -------------------- | ---------------- |
| Theme                      | Light / Dark / System   | System               | `localStorage`   |
| Display timezone           | IANA timezone list      | Browser default      | `localStorage`   |
| Numeric failure estimate   | Off / On                | **Off** (ADR-0001)   | `localStorage`   |

**The numeric failure estimate toggle** is the only config-gated feature (ADR-0001 §3). When enabled, it must render with its formula and parameters visible on the same surface, labeled as "this tool's model" rather than the game's (D-155).

#### 1.6 Global Constraints on All Inputs

These apply everywhere, always:

| Rule                                                  | Source            |
| ----------------------------------------------------- | ----------------- |
| No simulation, prediction, or race-day snapshots      | PRD §6.11         |
| No support-card database in Phase 1                   | PRD §6.9          |
| No event/banner calendar in Phase 1                   | PRD §6.6          |
| No trainee image upload                               | PRD §6.13         |
| No localStorage-authored runs                         | PRD §6.12         |
| Every number must be explainable from entered turns   | Planner Rule 4    |
| No unexplained recommended numbers                    | Planner Rule 5    |
| No equine vocabulary anywhere                         | CONSTRAINTS C-4   |
| Sentence case in all UI copy                          | CONSTRAINTS C-4   |
| No decorative emoji in labels                         | DESIGN §6.0       |

---

### 2. During a Career Run

#### 2.1 The Persistent Dashboard (Screen A)

This is the **always-visible frame** during a run. It never scrolls away.

**Layout: two regions**

| Region   | Contains                                                        | Behavior                       |
| -------- | --------------------------------------------------------------- | ------------------------------ |
| Left     | Run identity, turn chip, stat band, Energy gauge, fan readout   | Persistent, never collapses    |
| Right    | Turn timeline                                                   | Scrollable, grouped by phase   |

**The persistent resource strip** (D-170, D-230):

```text
[ 13 turn(s) left ]  Rice Shower · Trainee Umamusume · Unity Cup
                     Energy ▓▓▓▓▓░░░░  42  [Caution]   |   1,943 fans · target 3,000
```text

This strip is **scenario-composed** (D-220, D-240). The base items (turn, trainee, scenario, Energy, fans) are always present. Scenario-specific items appear only when the scenario has them:

| Scenario            | Additional strip items                            |
| ------------------- | ------------------------------------------------- |
| URA Finale          | None (baseline)                                   |
| Unity Cup           | Team Rank, Spirit Burst count                     |
| Trackblazer         | Grade Points (vs current objective), Shop Coins   |
| Our Grand Concert   | Baseline only (mechanics unextracted — D-241)     |

**Absent beats empty.** A URA run never shows an empty Team Rank slot. A Trackblazer run never shows a Race Calendar panel.

#### 2.2 The Turn Chip

**Behavior:**

- Torn-page calendar card (DESIGN §6.6)
- Shows: remaining turn count at `numeral-xl` in `blue-700`, phase countdown beneath
- **First in reading order and DOM** (D-42)
- Never hardcodes a total turn count (D-136) — shows turn number and turns-left only

**The turn chip is the orientation anchor.** A Trainer scanning the screen finds "where am I" before anything else.

#### 2.3 The Stat Band

**Behavior:**

- Six columns: Speed, Stamina, Power, Guts, Wit, Skill Points
- Each column: grade badge + `numeral-lg` value + `/cap` at `meta`
- **The value is the largest thing in the band. The label is not.** (P3)
- Subtle per-column tint from §3.6 table (near-invisible individually, reads as a group)

**The cap display is a stack, not a number** (D-162):

```text
Wit  1,340 / 1,800
     1,200 base · +600 scenario · +0 breakthrough · deck not tracked
```text

The denominator is the sum of terms the tool actually holds. Each component is labeled. **The disclosure line names what is included and what is not.** (D-162)

**Two markers, two meanings** (DESIGN §6.5, D-211):

- **1200 line**: the halved-gains threshold — a game mechanic, drawn as a dashed tick
- **Scenario ceiling**: the maximum holdable value, drawn as the bar end

They are different facts and must not collapse into one line.

#### 2.4 The Energy Gauge

**Behavior:**

- Fully rounded track, segmented fill sweeping cyan → green → lime → amber → red (DESIGN §6.15)
- Color encodes **position on the bar**, not the current level
- Label "Energy" in its own small pill to the left
- Value at `numeral-md` at the right end, tabular
- **2px `--color-risk` rule across the track at the 50 line**, always visible

**Band word** (not color alone — D-12):

| Band      | Range   | Word      | Treatment                |
| --------- | ------- | --------- | ------------------------ |
| Safe      | > 50    | Safe      | `ink` on `green-tint`    |
| Caution   | 30–50   | Caution   | `ink-strong` on `gold`   |
| Danger    | < 30    | Danger    | white on `risk`          |

**The 30 boundary is an owner ruling, not a game fact.** No source publishes a threshold below 50 (ADR-0001 §3). Where the Danger band is shown, it must be attributable to the tool's own model.

#### 2.5 The Turn Timeline

**Behavior:**

- Entries grouped under collapsible phase bars (Junior / Classic / Senior)
- Default state: current phase expanded, earlier phases collapsed (D-43)
- **Append-last, never re-sorts under the reader** (D-46)
- A new turn appears at the end and the view scrolls to it once

**Each entry is a card** (DESIGN §6.9):

- 40px circular turn-number disc overhangs the top-left corner
- `label-strong` title
- Hairline rule
- `body` prose with colored deltas: "Stamina went up by 14" with `up` in orange, 14 in orange; a decrease in blue

**Delta colors are the client's, not web convention** (D-133):

- Gains: **orange** (not green)
- Losses: **blue** (not red)
- Green means action, red means risk

#### 2.6 Guided Turn Input (Screen B)

This is where the brief's "not a web form" requirement lands.

**Behavior:**

- A turn is logged as a **sequence of decision cards**, each asking a single question in the game's voice (DESIGN §6.10)
- **One question per step**, in the client's decision order: training → outcome → skill event → note (D-52)
- **Preview before commit, always** (D-51, P2)
- **Committing is a separate explicit action** — selecting never writes

**The flow:**

```text
Step 1: "Which training are you focusing on this turn?"
        → Five banner options (Speed, Stamina, Power, Guts, Wit)
        → Each shows consequence preview
        → Select one

Step 2: "What happened?"
        → Outcome numbers the Trainer saw
        → Success/failure branch

Step 3: "Any skill event?"
        → Optional
        → Event name, choice taken, deltas

Step 4: "Any note?"
        → Optional free text
```text

**Critical: no numbered step sidebar.** A vertical 1-2-3-4 rail is a SaaS setup wizard and stops feeling like an in-game event (D-117). The flow carries a single `Step 1 of 3` line with three dots at the foot of the card stack, and nothing else.

**The preview is the input.** The Trainer is not filling in six number fields and hoping; they are picking the thing that produced the numbers they can see.

**Escape hatch:** direct field access exists for paste-and-correct, but it is secondary, not the default path (D-53).

**Keyboard-first:** 1-5 select a discipline, Enter confirms, Escape steps back. The full flow is completable without a pointer (D-55).

#### 2.7 The Five Training Options

Each is a **banner button** (DESIGN §6.1):

- White body, 1px accent outline, radius 10 on the left end only
- Right end terminates in an arrow cap: 40px gradient wedge pointing right
- Left end carries a 28px circular accent icon
- `ink-strong` label

**Selected state:** the whole body fills with `green` and the label goes white, or fills `gold` with `ink-strong`.

**What each option shows in preview:**

| Training   | Preview shows                                                   |
| ---------- | --------------------------------------------------------------- |
| Speed      | Speed gain, Power gain, SP gain, Energy cost                    |
| Stamina    | Stamina gain, Guts gain, SP gain, Energy cost                   |
| Power      | Power gain, Stamina gain, SP gain, Energy cost                  |
| Guts       | Guts gain, Speed gain, Power gain, SP gain, Energy cost         |
| Wit        | Wit gain, Speed gain, SP gain, **Energy recovery** (not cost)   |
| Rest       | +30 Energy recovery                                             |

**When Energy is below 50**, the advisory row appears (DESIGN §6.16):

```text
Wit costs 0 Energy and you are at 42. (GameWith 2026-09-25; source data 2023-02-25)
```text

The advisory **suggests; it never disables, dims, or reorders** the five options (D-155). The Trainer picks; the tool explains.

#### 2.8 Race Entry (F5)

**Appears only on a turn where the calendar holds an entry** (D-175). On turns with no race, the flow goes straight to training or rest.

**Behavior:**

- Reuses the event-panel shape (banner buttons, arrow-caps, preview-before-commit)
- Each race option shows: race name, tier badge, `fans_needed` vs current fan total, whether mandatory, Energy cost
- **Fan-locked races render disabled with their number, never hidden** (D-174)

**The panel must not rank races, project a placing, or estimate a win** (D-155). Eligibility is arithmetic over stored values and is fine. Outcome is not, and does not appear.

**Skip is a first-class option**, rendered as a banner like any other (D-176). "I am choosing not to run this" is a decision the Trainer makes deliberately.

#### 2.9 Scenario-Specific Panels

**Unity Cup additions:**

| Panel                 | Behavior                                                                                                                                                        |
| --------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Team Rank gauge       | Letter per stat + aggregate rank. Facility level derives from per-stat rank. "It's On!" reward derives from aggregate. Two different consumers of one widget.   |
| Spirit Burst roster   | Compact list of teammates, each carrying one of six states: chargeable, charged, held, normal-spent, Extreme-chargeable, Extreme-spent                          |
| Team Race countdown   | Under the objective timer, "N turns until next Team Race"                                                                                                       |

**Spirit Burst state machine** (DESIGN §6.24, D-223):

```text
chargeable → charged → held → normal spent → Extreme chargeable → Extreme spent
```text

**A spent-normal teammate is not inert.** They are the next Extreme candidate. Any "consumed" treatment (reduced opacity, strikethrough) is a lie on the fifth state.

**Trackblazer additions:**

| Panel               | Behavior                                                                                                                                                                                  |
| ------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Grade Point meter   | Progress against **current objective only**, with deadline date and "surplus does not carry over" note. A cumulative total would advertise a banking strategy the rules forbid (D-232).   |
| Shop Coin counter   | Balance + turns until rotation. The balance is the lesser of the two.                                                                                                                     |
| Epithet tracker     | Won-of-needed per route. Keep as a list, not a grid.                                                                                                                                      |
| Race Fatigue chip   | Consecutive-race count + consequence. **Vanishes after Late December** (D-230, D-231).                                                                                                    |

**No Race Calendar in Trackblazer.** Its absence is the scenario's defining UI fact (D-221).

#### 2.10 Event Resolution (F2, F3, F4)

**F2 Character Event / F3 Support Card Event:**

- Event panel slides in over the scene
- The scene stays visible behind it (no full-page scrim — D-131)
- Tag ribbon names the origin (`Support Card Event`, `Character Event`)
- Narrative block in `body` at generous line height
- Choices are banner buttons, each with its own preview
- Delta colors are the client's: gains orange, losses blue, never green and never red (D-133)
- Confirm is a separate action and is the only thing that writes

**F4 Scenario Event:**

- A single-choice panel, or a multi-step scenario sheet when the event genuinely has parts
- May show a step list; a standard turn may not (D-117)
- Keeps the tag ribbon, banner choices, and preview-before-commit rule
- Does not become a wizard with a progress rail and Back/Next footer

**A cap increase is never phrased as a stat increase** (D-191):

- Log reads: `Speed cap went up by 4`
- Never: `Speed went up by 4`

#### 2.11 The Race Calendar (URA, Unity Cup only)

**Applies only to scenarios with mandatory race goals** (D-221).

**Behavior:**

- 24-cell half-month grid under a Junior/Classic/Senior year tab bar (DESIGN §6.20)
- Cell states (the count is five because there are genuinely five things a Trainer needs to distinguish):

| State                        | Treatment                                                                     | Basis      |
| ---------------------------- | ----------------------------------------------------------------------------- | ---------- |
| Empty half-month             | `sunken` fill, muted plus glyph                                               | Measured   |
| Optional race, enterable     | `raised` fill, green plus, race artwork thumbnail                             | Measured   |
| Mandatory Goal race          | Red `Goal` pennant on cell corner, warm outline                               | Measured   |
| Scheduled, already entered   | Pink `Scheduled` pill over thumbnail                                          | Measured   |
| Locked by fans               | Dimmed, padlock + exact `fans_needed` figure                                  | Derived    |
| Locked by maiden gate        | Dimmed, dashed outline, distinct marker, "Win Debut or a Maiden race first"   | Derived    |
| Current turn                 | Pale yellow fill with warm outline                                            | Measured   |

**Lock state grammar — the three that matter:**

| State            | Fill                                        | Marker                                     | Text                                 | Why distinct                                                                            |
| ---------------- | ------------------------------------------- | ------------------------------------------ | ------------------------------------ | --------------------------------------------------------------------------------------- |
| Mandatory Goal   | `raised`, warm 2px outline                  | Red `Goal` pennant                         | Race name at `label-strong`          | Not a lock. An obligation. Reads heavier, never dimmer.                                 |
| Fan-locked       | `sunken`, 55% opacity                       | Padlock glyph                              | `1,000 fans` as a number             | Remedy is measurable. Trainer can see shortfall against header total.                   |
| Maiden-locked    | `sunken`, 55% opacity, **dashed** outline   | Distinct conditional marker, not padlock   | "Win Debut or a Maiden race first"   | Remedy is an event, not a quantity. Padlock would send Trainer to check wrong number.   |

**The dashed outline on the maiden lock is the whole difference** — opacity and a padlock already say "you lack a number," so the conditional gate must break the pattern rather than join it.

**Completed and entered races** use the client's own pink `Scheduled` pill and are never dimmed.

#### 2.12 Run Completion

**Behavior:**

- When the final turn is logged, the run transitions to `Completed` or `Retired`
- A summary view appears with:
  - Career Rank medal (entered by Trainer, never computed — Planner Rule 1)
  - Rating number (entered, never derived)
  - Final stats with grade badges
  - Aptitude grid (three rows: Track, Distance, Style)
  - Major Wins list (only from recorded races — never invented)
  - Fan class progression (Debut → Beginner → Bronze → Silver → Gold → Platinum → Star → Top Star → Legend)

**The Trainer records the rank letter and rating number as entered.** The tool never derives or forecasts them. A run may be saved with an empty rating; it may not be saved with a predicted one.

---

### 3. Input Taxonomy

#### 3.1 All User Inputs

| Input type                    | Where used                                                   | Behavior                                                      |
| ----------------------------- | ------------------------------------------------------------ | ------------------------------------------------------------- |
| **Banner button selection**   | Training options, race options, event choices                | Select shows preview; commit is separate                      |
| **Numeric entry**             | Outcome numbers, fan count, Energy, stats                    | Direct field access (escape hatch), paste-and-correct         |
| **Free text**                 | Notes, run name                                              | Optional, never required                                      |
| **Dropdown**                  | Sort order, scenario filter                                  | Single-select, immediate effect                               |
| **Toggle**                    | Theme, numeric failure estimate                              | Immediate effect, persisted to `localStorage`                 |
| **Search field**              | Skill search, catalog search                                 | Debounced, cancellable, never fires network request (NFR-1)   |
| **Keyboard**                  | 1-5 for disciplines, Enter to confirm, Escape to step back   | Full flow completable without pointer (D-55)                  |

#### 3.2 All Options

| Option                     | Values                                   | Source                           |
| -------------------------- | ---------------------------------------- | -------------------------------- |
| Training discipline        | Speed, Stamina, Power, Guts, Wit, Rest   | Game's five disciplines + Rest   |
| Race selection             | Available races from calendar            | Scenario calendar data           |
| Event choice               | A/B/C (typically 2-3 options)            | Game event structure             |
| Sort order                 | Date, turn depth, trainee name           | Run List                         |
| Filter                     | Scenario, status                         | Run List                         |
| Theme                      | Light, Dark, System                      | Settings                         |
| Numeric failure estimate   | Off, On                                  | Settings (ADR-0001)              |

#### 3.3 All Toggles

| Toggle                     | Location   | Default                  | Effect                                                                        |
| -------------------------- | ---------- | ------------------------ | ----------------------------------------------------------------------------- |
| Theme                      | Settings   | System                   | Switches token override before first paint                                    |
| Numeric failure estimate   | Settings   | **Off**                  | When on, shows percentage with formula visible, labeled "this tool's model"   |
| Phase collapse             | Timeline   | Current phase expanded   | Collapses/expands phase groups                                                |

#### 3.4 What Is NOT an Input

These are explicitly **not user inputs** in this tool:

| Not an input                 | Why                                                      | Source   |
| ---------------------------- | -------------------------------------------------------- | -------- |
| Race outcome prediction      | PRD §6.11 — no simulation                                |          |
| Stat projection              | Planner Rule 4 — deterministic over entered turns only   |          |
| Deck composition             | PRD §6.9 — no support-card database in Phase 1           |          |
| Event calendar               | PRD §6.6 — no event/banner calendar in Phase 1           |          |
| Trainee image                | PRD §6.13 — no image upload                              |          |
| Career Rank derivation       | Planner Rule 1 — no simulation; entered only             |          |
| Scenario selection mid-run   | Scenario is fixed at run creation                        |          |

---

### 4. State Machine: The Run Lifecycle

```text
[No Run] → [Active] → [Completed] or [Retired]
              ↑
              └── turns logged one at a time
```text

**States:**

| State       | Meaning                               | Allowed actions                           |
| ----------- | ------------------------------------- | ----------------------------------------- |
| Active      | Run in progress, turns being logged   | Log turn, view timeline, search skills    |
| Completed   | Final turn logged, summary shown      | View summary, view timeline (read-only)   |
| Retired     | Run ended early (failed objectives)   | View summary, view timeline (read-only)   |

**Transitions:**

- Active → Completed: final turn logged successfully
- Active → Retired: Trainer marks run as ended early (failed mandatory objective)
- No backward transitions — a completed run is immutable

**The run's scenario is fixed at creation.** It cannot be changed mid-run. The scenario determines:

- Which panels appear (D-220)
- Which facility leveling model applies
- Which cap set applies
- Which finale structure applies
- Which NPC set exists

---

### 5. Error, Empty, Loading States (Mandatory on Every Data View)

Per root `CONSTRAINTS.md` C-7 and PRD `ARCHITECTURE.md` §7, every data region implements all four states:

| State               | Treatment                                                                                                                                                                    |
| ------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Empty               | Panel with capsule header, quiet illustration slot, `title` line naming what belongs here, `body` line saying how to make one, one primary button. Never a bare "No data".   |
| Loading             | Panel's real shape with `idle`-filled skeleton rows. The client never shows a spinner over a blank page; it shows the frame you are about to fill.                           |
| Refresh in flight   | Catalog reads show last-fetched time + quiet progress mark (stale-while-revalidate). UI must not imply data vanished.                                                        |
| Error               | `crimson` rule on left of panel, icon, failed source's name, retry button. Failed fetch must never blank the catalog (NFR-2).                                                |

**The empty state is a designed screen, not a gap.** It is the first screen a Trainer ever sees.

---

### 6. Motion Contract

| Interaction          | Spec                                                                               |
| -------------------- | ---------------------------------------------------------------------------------- |
| Button press         | 120ms, translateY 1px, shadow lift to press                                        |
| Card select          | 140ms ease-out, glow ring fades in, fill crossfades                                |
| Choice card expand   | 180ms, preview slides 8px and fades; card does not move                            |
| Commit               | 200ms; new turn entry pushes in from bottom of timeline, gain bubble fires first   |
| Panel open           | 160ms ease-out, scale 0.98 to 1 plus fade. No slide-in sheets.                     |
| Phase collapse       | 180ms height, 120ms chevron rotate                                                 |
| Number change        | Count-up over 240ms, only on the stat band                                         |

**Rules:**

- Nothing animates that is not responding to the Trainer (no idle motion, no ambient drift, no entrance animation on page load)
- No motion longer than 240ms
- `prefers-reduced-motion: reduce` collapses every transition to a 1ms crossfade, disables count-up, keeps state change legible without movement (D-92)
- Motion never carries information alone — a state change must be legible with animation disabled (D-93)

---

### 7. Accessibility Contract

| Requirement            | Implementation                                                                                   |
| ---------------------- | ------------------------------------------------------------------------------------------------ |
| Contrast               | Every text pair passes WCAG 2.1 AA at its size (§3.4). Chrome steps never carry text.            |
| Focus                  | 3px `green` ring at 35% alpha, always visible, never `outline: none` without replacement         |
| Target size            | 44px minimum on anything clickable                                                               |
| Color independence     | Every state carries a word. Grade badges show the letter. Deltas show sign and direction word.   |
| Keyboard               | Guided flow fully operable without pointer. Turn timeline is a list with roving focus.           |

**Amended 2026-10-03: the WCAG 2.1 AA mandate this contract cites is superseded. WCAG 2.2 Level AA is the operative mandate, per the owner's ruling of 2026-10-03.**
| Screen readers       | Stat band is a table or list with explicit labels. Gain bubbles are `aria-live="polite"`.      |
| Decorative exclusion | Faceted page field and lattice bleed are decorative CSS, not exposed to assistive technology.  |

---

### 8. What This App Never Does

| Never                                              | Source             |
| -------------------------------------------------- | ------------------ |
| Predict race outcomes                              | PRD §6.11          |
| Simulate training results                          | PRD §6.11          |
| Store support-card decks                           | PRD §6.9           |
| Show event/banner calendars                        | PRD §6.6           |
| Accept trainee images                              | PRD §6.13          |
| Author runs in localStorage                        | PRD §6.12          |
| Require auth or accounts                           | PRD §6.1           |
| Use equine vocabulary                              | CONSTRAINTS C-4    |
| Show unexplained recommended numbers               | Planner Rule 5     |
| Derive Career Rank or Rating                       | Planner Rule 1     |
| Display a race-day snapshot                        | PRD §6.11          |
| Invent scenario chrome for undescribed scenarios   | D-241              |
| Merge two servers' data                            | Reference doc §1   |
| Promote unverified items into UI copy              | D-20               |

---

This is the behavioral contract. Every input, option, toggle, state, and transition is sourced from the design documents. Where a behavior is not specified here, it is either out of Phase 1 scope or has not been decided — and the absence is deliberate, not an omission.

---

## Part 3: Scenario-Specific User Flows & Frontend Specifications

> **Reviewed, largely sound, not merged — 2026-09-27.** Audit: `docs/design-research/FRONTEND-SPEC-DIVERGENCE.md` §6.
> Most of this traces to the repo's sourced tables (Trackblazer caps in the corrected order, shop prices, the
> 150-coin permanent facility raise, energy bands with the "30 is ours, not the game's" caveat). Fix before use: the
> Race Fatigue chip prints single-source percentages as bare numbers (D-230), the resource strip carries more widgets
> than §6.23's zero-to-five cap, "3+ of 5 to win" misreads a win-odds estimate as a victory rule, "Anklet" is
> Ankle Weights, and "inheritance" as the system noun should be Inspiration / Legacy Select.

### Unity Cup and Trackblazer

All specifications below are grounded in `UMAMUSUME_REFERENCE.md`, `docs/scenarios/02-unity-cup.md`, `docs/scenarios/04-trackblazer-umaguide.md`, `docs/scenarios/05-trackblazer-gametora.md`, `docs/scenarios/06-unity-cup-gametora.md`, `SCENARIO-DIFFERENCES.md`, and `DESIGN.md`. Nothing here simulates, predicts, or recommends (PRD §6.11, Planner Rules 1–5). Every number rendered is Trainer-entered or a sourced constant.

---

## PART 1: UNITY CUP

### 1.1 Scenario Identity

Unity Cup transforms the solo training loop into a team-management problem. The Trainer is no longer optimizing one Umamusume in isolation; they are building a roster, managing Spirit Burst charges across teammates, and fielding sub-teams for periodic Team Races. The facility level system is entirely replaced: personal repetition no longer matters; the team's aggregate stat rank determines everything.

**What changes from the baseline (URA Finale):**

| Axis                           | URA Finale                  | Unity Cup                            |
| ------------------------------ | --------------------------- | ------------------------------------ |
| Facility level driver          | Repeat one stat 4×          | Team aggregate stat rank per stat    |
| Scenario currency              | None                        | Team Rank, Spirit Burst count        |
| Periodic event                 | None                        | Team Race every 6 months (5 races)   |
| Scenario Link                  | Aoi Kiryuin (1 character)   | 5 characters, chosen via Team Name   |
| Acting chairman                | Akikawa                     | Riko Kashimoto (Akikawa absent)      |
| Stat caps                      | 1400 × 5                    | 1300 × 4, Wit 1800                   |
| April Unique Skill bond gate   | 3-bar Akikawa friendship    | No bond condition (Akikawa absent)   |

**What does NOT change:** the five training disciplines, Energy/Mood system, race mechanics, skill acquisition, inheritance, Summer Camp (both years, 4 turns, all facilities Lv5).

---

### 1.2 User Flow: Team Composition & Spirit Burst Management

This is the core loop unique to Unity Cup. It runs continuously alongside normal training turns.

#### Flow UC-1: Special Training Decision

```text
TRAINER OPENS TRAINING SCREEN
│
├─ 1. Read the facility tabs.
│     Each tab now shows OCCUPANCY: which teammates are present,
│     each marked with a white flame icon if chargeable.
│     The facility level is NOT derived from repetition count.
│     It reads from the team's aggregate stat rank for that stat.
│     Example: Team Speed rank A → Speed facility Lv4.
│
├─ 2. Count the flames on each tile.
│     ┌─────────────────────────────────────────────────────┐
│     │ RULE (post-rework):                                 │
│     │ • 0 flames → normal training, no team benefit       │
│     │ • 1 flame  → benefits that TEAMMATE only            │
│     │              (their Spirit gauge fills, their stats  │
│     │              rise, but the TRAINEE gains nothing     │
│     │              beyond base training)                   │
│     │ • ≥2 flames → TRAINEE gains bonus stats + SP        │
│     │              (this is the threshold that matters)    │
│     └─────────────────────────────────────────────────────┘
│     The ≥2 threshold is a display state, not just a number.
│     A 1-flame tile must visually communicate "no trainee bonus."
│
├─ 3. Check for Spirit Burst readiness.
│     If any teammate's Spirit gauge is full, a BURST-READY
│     indicator appears on their flame icon.
│     The Trainer decides: trigger now, or hold for a
│     better facility?
│     ┌─────────────────────────────────────────────────────┐
│     │ HOLDING IS A REAL STRATEGY:                         │
│     │ A Burst can be held until the teammate lands on     │
│     │ the facility the Trainer actually wants boosted.    │
│     │ The Extreme Spirit Burst follows on the NEXT        │
│     │ Unity Training with that teammate, so the choice    │
│     │ of facility for the Burst also determines where     │
│     │ the Extreme lands.                                  │
│     └─────────────────────────────────────────────────────┘
│
├─ 4. Check for Extreme Spirit Burst availability.
│     After a normal Burst has fired, the teammate enters
│     EXTREME-CHARGEABLE state. On their next Unity Training,
│     an Extreme Spirit Burst may appear.
│     ┌─────────────────────────────────────────────────────┐
│     │ EXTREME BURST EFFECTS:                              │
│     │ • Everything a normal Burst does, plus:             │
│     │ • Sets that facility's failure chance to 0%         │
│     │   (scoped to the facility holding the Burst,        │
│     │    NOT all facilities)                              │
│     │ • Raises the teammate's stat caps                   │
│     │ • Grants a hint for the facility-specific           │
│     │   "Ignited Spirit" skill (Lv1, scaling with         │
│     │   card's Hint Level bonus)                          │
│     └─────────────────────────────────────────────────────┘
│     When an Extreme Burst is active on a facility, the
│     failure-risk indicator for that facility goes QUIET.
│     Not "low." Quiet. Removing the warning entirely is
│     the correct rendering because the failure rate is
│     sourced as 0%.
│
├─ 5. Preview the training outcome.
│     Preview shows:
│     • Stat gains for the trainee (base + Special Training bonus)
│     • SP gained
│     • Energy cost (NO energy penalty on Special Training;
│       the pre-rework penalty was removed 2026-07-01)
│     • Which teammates will gain stats and Spirit gauge
│     • If a Burst will trigger: Burst gains preview
│     • If scenario-linked cards are present: +1 per stat
│       (counted separately for Special Training vs Burst gains)
│
└─ 6. Confirm or cancel.
      Confirm → resolve turn → timeline entry appended.
      Cancel → return to training screen, nothing written.
```text

#### Flow UC-2: Spirit Burst Resolution

```text
BURST TRIGGERS (on the training turn where the gauge is full
and the Trainer confirms training on that tile)
│
├─ 1. Normal Burst fires.
│     Three things happen simultaneously:
│     a) Teammate gets a large stat boost
│     b) Trainee gets a moderate stat boost
│     c) Trainee gets a SKILL HINT
│
│     HINT SOURCING (post-rework):
│     ┌─────────────────────────────────────────────────────┐
│     │ The hint is NOT random.                             │
│     │ It draws from that support card's own hint pool.    │
│     │ For non-deck teammates: R-card pool substitute.     │
│     │ If pool exhausted: falls back to a random hint      │
│     │ based on the trainee's A-rank aptitudes.            │
│     │                                                     │
│     │ Hint level = card's hint bonus + 2                  │
│     │ (minimum Lv2 always)                                │
│     │ +1 more if the card is scenario-linked              │
│     └─────────────────────────────────────────────────────┘
│
├─ 2. Teammate enters "normal burst spent" state.
│     They are NOT consumed. They are NOT inert.
│     They enter EXTREME-CHARGEABLE state.
│     Rendering a spent teammate as a dead end is wrong.
│
├─ 3. If the Burst was on the Wit facility:
│     +5 extra Energy recovery (on top of the base Wit refund).
│
├─ 4. Burst count increments.
│     This count feeds the scenario skill ladder (UC-5).
│     Both normal and Extreme Bursts count toward the total.
│
└─ 5. Timeline entry appended.
      Entry shows: which teammate burst, what stats were gained,
      which skill hint was received, Burst count running total.
```text

#### Flow UC-3: Team Race (every 6 months)

```text
TEAM RACE COUNTDOWN REACHES ZERO
(countdown visible under the standard objective timer)
│
├─ 1. Team composition screen.
│     The Trainer assigns teammates to 5 categories:
│     Sprint, Mile, Medium, Long, Dirt.
│     Each category fields a sub-team of 3.
│     ┌─────────────────────────────────────────────────────┐
│     │ TEAMMATE STATS:                                     │
│     │ Raised ONLY through Special Training.               │
│     │ NOT dependent on the support card's own level       │
│     │ or limit break.                                     │
│     │ The composition display must NOT imply card level   │
│     │ drives team performance.                            │
│     └─────────────────────────────────────────────────────┘
│
├─ 2. Opponent selection.
│     3 NPC teams offered, strongest to weakest.
│     Each shows the league rank change if beaten.
│     A circle-based win-odds estimate is shown per category.
│     ┌─────────────────────────────────────────────────────┐
│     │ The odds estimate is the GAME's own display.        │
│     │ The tool records what the Trainer sees.             │
│     │ The tool does NOT compute its own odds.             │
│     │ (Planner Rule 1: no simulation)                     │
│     └─────────────────────────────────────────────────────┘
│     Guidance from source: aim for ≥3 circles total.
│     Losing decreases league rank.
│
├─ 3. Race resolution.
│     5 races run. The Trainer's trainee runs in one of them
│     (the "main race"). The other 4 are resolved by the game.
│     The tool records:
│     • Per-category result (win/loss per sub-team)
│     • Overall result (need 3+ of 5 to win)
│     • League rank change
│
├─ 4. Post-race.
│     New random teammates join the roster.
│     If the race was LOST:
│     ┌─────────────────────────────────────────────────────┐
│     │ ALARM CLOCK RETRY (post-rework):                    │
│     │ The Trainer may spend an Alarm Clock to retry       │
│     │ the lost Team Race.                                 │
│     │ The race log must allow a loss to be SUPERSEDED,    │
│     │ and should say so rather than silently overwriting.  │
│     │ A retry is a visible, recorded action.              │
│     └─────────────────────────────────────────────────────┘
│
└─ 5. Timeline entry appended.
      Entry shows: opponent team name, per-category results,
      overall result, league rank change, new teammates joined.
```text

#### Flow UC-4: Team Name Selection (Junior Year, 2nd half September)

```text
TEAM NAME EVENT FIRES
│
├─ 1. The Trainer chooses a team name from 5 options.
│     Each option is bound to a scenario-linked character:
│
│     Character              Team Name          Reward Skill (on beating Zenith)
│     ─────────────────────────────────────────────────────────────────
│     Taiki Shuttle          Happy Hoppers       Mile Maven
│     Matikanefukukitaru     Sunny Runners       Clairvoyance
│     Haru Urara             Carrot Pudding      Indomitable
│     Rice Shower            Blue Bloom          Cooldown
│     None of the above      Team Carrot         No Stopping Me!
│
│     ┌─────────────────────────────────────────────────────┐
│     │ ELIGIBILITY:                                        │
│     │ The linked character must be the trainee OR an      │
│     │ equipped support card. Otherwise that team name     │
│     │ option is not selectable.                           │
│     └─────────────────────────────────────────────────────┘
│
├─ 2. The Trainer confirms.
│     The team name is fixed for the run.
│     The reward skill is NOT granted yet.
│     It is granted only upon beating Team Zenith in the Finals.
│
└─ 3. Timeline entry appended.
      Entry shows: team name chosen, linked character.
```text

#### Flow UC-5: Scenario Skill Ladder

```text
TOTAL BURST COUNT IS TRACKED ACROSS THE RUN
(normal Bursts + Extreme Bursts combined)
│
├─ Ladder thresholds (post-rework):
│
│   Total Bursts   Reward
│   ─────────────────────────────────────────────
│   4–6            White hint Lv1, +10 matching stat, +10 SP
│   7–9            White hint Lv3, +20 matching stat, +20 SP
│   10–12          Gold hint Lv1, +30 matching stat, +30 SP
│   13+            Gold hint Lv3, +40 matching stat, +40 SP
│
│   The matching stat = the team's HIGHEST stat rank.
│
├─ White-rarity skills come from Extreme Spirit Bursts.
│   Gold-rarity skills (plus extra white hints) come from
│   the scripted "Team Zenith Declares War" event
│   (Senior Year, late November).
│
└─ The progress meter's denominator is normal + Extreme combined.
   This changed in the rework. A meter counting only normal
   Bursts would show the wrong progress.
```text

#### Flow UC-6: Unity Cup Finals

```text
AFTER THE 4TH TEAM RACE
│
├─ 1. The Trainer faces Team Zenith
│     (Riko Kashimoto's team: Little Cocon, Bitter Glasse).
│
├─ 2. Elite Team check (4th Team Race only):
│     If ALL of the following are true:
│     • League rank ≥ 10
│     • Team Rank ≥ A
│     • ≥ 1 Extreme Spirit Burst triggered
│     → An Elite Team appears (pink/purple background,
│       named after Greek deities).
│     Beating it unlocks a STRENGTHENED Team Zenith.
│
├─ 3. Finals resolution.
│     If the Trainer beats Zenith at Rank S:
│     → Little Cocon and Bitter Glasse appear as opponents
│       in the URA-style final race.
│
├─ 4. Team Name reward.
│     Beating Zenith grants the team name's gold-rarity skill.
│
└─ 5. Post-finals:
      URA-style final races proceed (qualifier → semifinal → final).
      Same structure as URA Finale for the last 3 races.
```text

#### Flow UC-7: Unique Skill Level-Ups (Unity Cup variant)

```text
SAME FAN THRESHOLDS AS URA FINALE:
  Valentine's (Senior, early Feb):  60,000 Turf / 40,000 Dirt
  Early April (Senior):             70,000 Turf / 60,000 Dirt
  Christmas (Senior, late Dec):     120,000 Turf / 80,000 Dirt

DIFFERENCE: The April level-up has NO bond condition.
  URA Finale requires 3-bar Akikawa friendship.
  Unity Cup does not, because Akikawa is absent
  (Riko Kashimoto stands in as acting chairman).

The tool must NOT show an Akikawa bond requirement
in the April gate for Unity Cup runs.
```text

---

### 1.3 Unity Cup Frontend Specification

#### FC-UC-1: Scenario Resource Strip (replaces baseline strip)

The persistent header strip gains Unity Cup-specific elements.

```text
┌──────────────────────────────────────────────────────────────────────┐
│ [ 13 turn(s) left ]  Rice Shower · Trainee · Unity Cup              │
│                                                                      │
│ Energy ▓▓▓▓▓░░░░ 42 [Caution]  │  Fans 1,943 · target 3,000       │
│                                                                      │
│ Team Rank: B  │  Spirit Bursts: 4/13+  │  League Rank: 7            │
│ Next Team Race: 3 turns                                              │
└──────────────────────────────────────────────────────────────────────┘
```text

| Element                    | Source                                             | Rendering rule                                                                                                |
| -------------------------- | -------------------------------------------------- | ------------------------------------------------------------------------------------------------------------- |
| Team Rank                  | Trainer-entered after each rank-up                 | Letter chip (G through S+). Uses the grade badge palette from §6.7. S+ gets a distinct visual step above S.   |
| Spirit Burst count         | Running total of normal + Extreme Bursts           | Fraction display: `current / next-threshold`. The denominator changes at each ladder rung (6, 9, 12, 13+).    |
| League Rank                | Trainer-entered after each Team Race               | Numeral. Rises with opponent strength, falls on loss.                                                         |
| Next Team Race countdown   | Derived from turn count and the 6-month interval   | "N turns" label. Sits under the objective timer, matching the client's placement.                             |

**What is NOT on the strip:** Energy, Fans, turn chip, and scenario name remain identical to the baseline. The strip is composed, not forked (D-220).

#### FC-UC-2: Facility Tab Modification

Each facility tab gains two additions over the baseline:

**a) Occupancy display.** Teammates present on the tile are shown as small circular avatars. Each chargeable teammate carries a white flame icon. The flame count is the primary signal.

| Flame count     | Visual treatment                                   | Meaning                                                                             |
| --------------- | -------------------------------------------------- | ----------------------------------------------------------------------------------- |
| 0               | No flames. Standard tile.                          | Normal training. No team benefit.                                                   |
| 1               | One flame. Subtle.                                 | Benefits that teammate only. No trainee bonus. The tile should NOT glow or pulse.   |
| ≥2              | Multiple flames. The tile gets a warm highlight.   | Trainee gains bonus stats + SP. This is the actionable state.                       |
| Burst-ready     | Flame icon gains a filled gauge ring.              | Spirit Burst will trigger on this tile if the Trainer confirms.                     |
| Extreme-ready   | Flame icon gains a purple ring.                    | Extreme Spirit Burst available. Failure risk for this facility is 0%.               |

**b) Facility level source label.** The level chip must state its cause:

```text
Speed Lvl 4 ← Team Rank A
```text

Not just `Lvl 4`. In URA Finale, the cause is repetition. In Unity Cup, it is always the team rank. A level chip without its cause is misleading (D-222).

#### FC-UC-3: Teammate Roster Panel

A collapsible panel on the right side of the dashboard, below the timeline.

| Column            | Content                                                                                       |
| ----------------- | --------------------------------------------------------------------------------------------- |
| Teammate name     | From support card, story character, or random recruit                                         |
| Type badge        | Support card type (Speed/Stamina/Power/Guts/Wit/Pal/Group)                                    |
| Spirit gauge      | 0–100 fill bar. Orange at full (burst-ready).                                                 |
| Burst state       | One of 6 states: chargeable, charged, held, normal-spent, Extreme-chargeable, Extreme-spent   |
| Stats             | Teammate's own Speed/Stamina/Power/Guts/Wit (raised only through Special Training)            |
| Scenario-linked   | Green pill if the teammate is one of the 5 linked characters                                  |

**State machine rendering:**

```text
chargeable ──→ charged ──→ held ──→ normal spent ──→ Extreme chargeable ──→ Extreme spent
   (white)     (filled)   (deliberately              (NOT a dead end;       (final state
                gauge       untriggered)               purple ring)           for this run)
                full)
```text

A "spent" teammate must NEVER render as consumed or resettable. They are one tier spent, one tier pending (D-223).

#### FC-UC-4: Team Race Composition Screen

Full-width overlay, triggered when the Team Race countdown reaches zero.

```text
┌──────────────────────────────────────────────────────────────┐
│  TEAM RACE — Round 2 of 4                                    │
│  Opponent: [strongest] / [middle] / [weakest]                │
│  League rank change if won: +2                               │
│                                                              │
│  Sprint    [avatar] [avatar] [avatar]   ○ ○ ●               │
│  Mile      [avatar] [avatar] [avatar]   ○ ● ●               │
│  Medium    [avatar] [avatar] [avatar]   ● ● ●               │
│  Long      [avatar] [avatar] [avatar]   ○ ○ ○               │
│  Dirt      [avatar] [avatar] [avatar]   ● ○ ○               │
│                                                              │
│  Win odds estimate: 3 of 5 categories favored                │
│                                                              │
│  [ Confirm Lineup ]    [ Cancel ]                            │
└──────────────────────────────────────────────────────────────┘
```text

Circle indicators: ○ = disadvantaged, ● = favored. These are the GAME's own estimates, recorded by the Trainer. The tool does not compute them.

**Post-race state:** If lost, an `Alarm Clock retry` button appears. Pressing it supersedes the loss entry in the timeline. The original loss is NOT deleted; it is marked as superseded.

#### FC-UC-5: Scenario Skill Progress Meter

A horizontal progress bar in the dashboard, below the resource strip.

```text
Spirit Burst Progress
████████████████░░░░  11 of 13+
Next reward: Gold hint Lv1, +30 [highest team stat], +30 SP
```text

The denominator shifts at each threshold (6 → 9 → 12 → 13+). The bar must reflect the CURRENT threshold, not a fixed maximum.

The matching stat is determined by the team's highest stat rank. This is displayed as text, not assumed.

#### FC-UC-6: Unique Skill Gate Display (Unity Cup variant)

Same three gates as URA Finale, but the April gate omits the bond requirement:

```text
Valentine's Gate   60,000 / 40,000 fans   [ reached / not reached ]
April Gate         70,000 / 60,000 fans   [ reached / not reached ]
                   (no bond requirement in Unity Cup)
Christmas Gate     120,000 / 80,000 fans  [ reached / not reached ]
```text

The tool must NOT render an Akikawa bond bar in the April gate for Unity Cup runs. Rendering it would state a requirement that does not exist in this scenario (Akikawa is absent; Riko Kashimoto stands in).

---

## PART 2: TRACKBLAZER

### 2.1 Scenario Identity

Trackblazer inverts the relationship between training and racing. In URA Finale and Unity Cup, racing serves training (fans, skill points, objectives). In Trackblazer, training serves racing. The scenario has no mandatory race goals. Instead, the Trainer accumulates Grade Points through racing to meet deadlines, earns Shop Coins through race placement, and spends them in a rotating shop. The finale is a points league, not an elimination bracket.

**What changes from the baseline:**

| Axis                | URA Finale                                   | Trackblazer                                            |
| ------------------- | -------------------------------------------- | ------------------------------------------------------ |
| Goal structure      | Fixed mandatory race goals                   | Grade Point deadlines (no race goals)                  |
| Scenario currency   | None                                         | Grade Points, Shop Coins                               |
| Shop                | None                                         | In-run shop, restocks every 6 turns                    |
| Facility level      | Repeat 4×                                    | Repeat 4× PLUS permanent +1 from shop                  |
| Scenario Link       | Aoi Kiryuin (1)                              | None (the only scenario with zero linked characters)   |
| Secret events       | Yes                                          | Do NOT trigger                                         |
| Epithets            | None                                         | Race route bonuses (stat + hint rewards)               |
| Rival Races         | None                                         | From Early August Junior Year                          |
| Finale              | URA elimination (qualifier → semi → final)   | Twinkle Star Climax: 3-race points league              |
| Stat caps           | 1400 × 5                                     | 1200 / 1900 / 1200 / 1200 / 1500                       |

**What does NOT change:** the five training disciplines, Energy/Mood system, race mechanics, skill acquisition, inheritance, Summer Camp.

**What is explicitly absent:** No Race Calendar panel. No mandatory race goals. No Scenario Link. No secret character events. These absences are the scenario's defining UI facts (D-221).

---

### 2.2 User Flow: Grade Point Objectives

#### Flow TB-1: Grade Point Tracking

```text
THE RUN HAS 4 OBJECTIVES (replacing URA's race goals)
│
├─ Objective 1: Run the Debut Race
│   Timing: Late June, Junior Year
│   Same as URA: mandatory debut.
│   ⚠ The Debut Race costs Energy in Trackblazer.
│
├─ Objective 2: Earn Grade Points
│   Deadline: End of Junior Year (Late December)
│   ┌─────────────────────────────────────────────────────┐
│   │ THRESHOLD VARIES BY APTITUDE:                       │
│   │ Standard turf:           60 Grade Points            │
│   │ High-dirt / low-turf:    30 Grade Points            │
│   │   (e.g., Haru Urara)                                │
│   │ Narrow-range turf:       60 Grade Points            │
│   │   (e.g., Curren Chan — obj 3 drops to 200)         │
│   └─────────────────────────────────────────────────────┘
│
├─ Objective 3: Earn Grade Points
│   Deadline: End of Classic Year (Late December)
│   Standard: +300. Dirt-leaning: +200. Narrow turf: +200.
│
├─ Objective 4: Earn Grade Points
│   Deadline: End of Senior Year (Late December)
│   Standard: +300. All tracks: +300.
│
└─ CRITICAL RULE: Surplus does NOT carry over.
   Each objective period starts from zero.
   Over-shooting one deadline cannot fund the next.
   A Grade Point meter that shows a running total
   across periods would state the wrong thing.
```text

#### Flow TB-2: Grade Point Earning (through racing)

```text
TRAINER ENTERS A RACE
│
├─ 1. Race selection.
│     No mandatory goals. The Trainer picks any available race.
│     The race list shows the Grade Point yield per race grade:
│
│     Race Grade    Grade Points (1st place)
│     ─────────────────────────────────────
│     G1            100
│     G2            80
│     G3            60
│     OP            40
│     Pre-OP        20
│
│     Placement modifier applies:
│     1st: 100% · 2nd: 60% · 3rd: 40% · 4th–5th: 20% · 6th+: 10%
│
├─ 2. Race resolution.
│     The Trainer records the placement.
│     Grade Points earned = base × placement modifier.
│     Shop Coins earned simultaneously (see TB-3).
│
├─ 3. Grade Point meter updates.
│     Shows progress against the CURRENT objective only.
│     Resets to zero at each deadline.
│     Does NOT show a cumulative total.
│
└─ 4. Timeline entry appended.
      Entry shows: race name, grade, placement,
      Grade Points earned, Shop Coins earned.
```text

#### Flow TB-3: Shop Coins & The Special Shop

```text
COINS ARE EARNED THROUGH RACE PLACEMENT
│
├─ Coin yield (independent of race grade):
│   1st: 100 · 2nd–3rd: 60 · 4th–5th: 30 · 6th+: 0
│
│   ⚠ A Pre-OP win pays the same coins as a G1 win.
│   ⚠ 6th place or worse pays ZERO coins.
│   Losing has a direct economic penalty.
│
├─ THE SPECIAL SHOP
│   Unlocks after the Debut Race.
│   Restocks every 6 turns (timer visible in the shop UI).
│   Max 5 copies of any single item held at once.
│   Occasional Limited items (own availability window, flagged top-right).
│   Occasional Sales (10–20% off, flagged top-left).
│
├─ ITEM CATEGORIES AND PRIORITIES (from uma.guide):
│
│   Must Buy:
│   • +15 stat (30 coins), +7 stat (15 coins)
│   • +1 Mood (30c), +2 Mood (55c)
│   • +20/+40/+65 Energy items
│   • +35% Race Bonus (Hammer, 40c), +20% (25c)
│   • +60% Training Bonus (Megaphone, 70c)
│   • Card shuffle (Whistle, 20c)
│   • +50% single-stat training (Anklet, 50c)
│   • 0% training failure for 1 turn (Good-Luck Charm, 40c)
│
│   Situational:
│   • +100 Energy / −1 Mood (70c) — pair with a Mood item
│   • +5 all support bonds (BBQ, 40c)
│   • Heal all conditions (40c)
│   • Heal Skin Outbreak specifically (15c) — common in Trackblazer
│
│   Skip / Trap:
│   • +3 stat (10c) — low value filler
│   • +20% Training Bonus (40c) — outclassed by 60% Megaphone
│   • Max Energy boosters — generally skip
│   • Fast Learner status (280c) — TRAP. Same coins in Hammers
│     across the run yield more effective SP.
│   • Charming status (150c) — only worth it in the very first shop.
│
├─ MULTI-TURN ITEM RULES:
│   • Cannot re-use while active.
│   • Buying a weaker item after a stronger one OVERWRITES it.
│   • Buying a stronger item first BLOCKS the weaker until expiry.
│   The order of two purchases is a real, lossy decision.
│
└─ END-OF-RUN RULE:
    Unspent coins are worthless.
    Twinkle Star Climax races pay NO coins.
    The final shop before the Climax is the last chance to spend.
```text

#### Flow TB-4: Epithet Route Tracking

```text
EPITHETS ARE RACE ROUTE BONUSES
Winning every race in a named route grants bonus stats or skill hints.
│
├─ MAJOR ROUTES (shape the racing schedule):
│
│   Tiara Route (Oka Sho → Japanese Oaks → Shuka Sho)
│     Lady: +10 to 2 random stats
│     Heroine: Lady + QEII Cup (Classic): +10 to 2
│     Goddess: Lady + Victoria Mile + Hanshin JF + both QEII: +15 to 2
│     Mile a Minute: all unique Mile Turf G1s → Mile Straightaways hint +1
│
│   Classic Route (Satsuki Sho → Japanese Derby → Kikuka Sho)
│     Stunning: +10 to 2
│     Incredible: Stunning + JC or Arima (Classic): +15 to 2
│     Phenomenal: Stunning + 2 of 6 specified races: +15 to 2
│
│   Sprint/Mile Route
│     Breakneck Miler: NHK Mile + Yasuda + Mile Championship: +15 to 2
│     Sprint Go-Getter: Takamatsu + Sprinters: +10 to 2
│     Sprint Speedster: all 4: +15 to 2
│
│   Spring/Autumn Route
│     Spring Champion / Fall Champion / Shield Bearer: +10 to 2 each
│     Legendary: (Lady or Stunning) + both Champions → Homestretch Haste hint +1
│
│   Dirt Route (win-count based)
│     Dirty Work (5 wins) → Playing Dirty (10) → Eat My Dust (15)
│     Dirt G1 ladder: 3 → 4 → 5 → 9 wins
│     Dirt Sprinter: both JBC Sprints
│     Kicking Up Dust: Unicorn S + Leopard S + Japan Dirt Derby
│
├─ SECONDARY ROUTES:
│   Win-count and location-based (Pro Racer, regional titles, etc.)
│   Generally acquired as byproducts, not deliberately targeted.
│
├─ APTITUDE WARNING:
│   Most graded races are Mile and Medium distance.
│   G1 distribution: Sprint 3, Mile 10, Medium 14, Long 3.
│   G3 distribution: Sprint 18, Mile 33, Medium 17, Long 1.
│   A trainee without at least C (ideally B) Mile and Medium
│   aptitude will miss a large share of available rewards.
│
└─ TRACKING UI:
    Each active route shows a won-of-needed count.
    Example: "Tiara Route: 2 of 3 races won"
    Completed routes show the reward received.
    Routes are a LIST, not a grid of equal-sized cards.
    There are dozens of epithets; only a handful are live routes.
```text

#### Flow TB-5: Rival Races

```text
RIVAL RACES APPEAR RANDOMLY FROM EARLY AUGUST, JUNIOR YEAR
Marked by a red/blue "VS" speech-bubble icon on the race button.
│
├─ 1. Participation.
│     Simply entering has a chance to add new items to the shop.
│
├─ 2. Winning (placing 1st).
│     Increases the odds of new shop items.
│     Additionally grants EITHER:
│     • A skill hint (tied to the race distance or running style used), OR
│     • +5 to 2 random stats
│
├─ 3. Draw condition.
│     If the Trainer out-places the rival but does NOT take 1st,
│     it is a DRAW. No hint. No stat bonus.
│     Only 1st place counts as a win.
│     Same logic as Team Trials and Unity Cup.
│
├─ 4. Skill hint requirement.
│     The hinted skill requires C aptitude or better in that distance.
│     A rival race at a distance the trainee is D-rated in
│     cannot produce a hint. The UI must not imply otherwise.
│
└─ 5. Timeline entry appended.
      Entry shows: rival name, result (win/draw/loss),
      reward received (hint name or stat gains).
```text

#### Flow TB-6: Facility Level (Trackblazer variant)

```text
SAME BASE RULE AS URA: repeat one stat 4× to level up, max Lv5.

PLUS: Permanent +1 levels purchasable from the shop.
  Item: Training Application (150 coins)
  Effect: +1 facility level, PERMANENT for the run.
  Stacks with natural leveling.

This makes Trackblazer the ONLY scenario where facility level
is partly bought rather than purely earned.
The level chip must state its cause:

  Speed Lvl 5 ← 4× repetition + 1 purchased

Not just "Lvl 5." The purchased path is unique to Trackblazer (D-222).
```text

#### Flow TB-7: Race Fatigue Management

```text
CONSECUTIVE RACES INCREASE FATIGUE RISK
│
├─ Risk table (sourced from uma.guide):
│
│   Consecutive   Mood Down     Skin Outbreak   3 Stats −10
│   Races         (1+E / 0E)   (1+E / 0E)     (1+E / 0E)
│   ─────────────────────────────────────────────────────────
│   1             0% / 15%      0% / 4%        0% / 0%
│   2             0% / 33%      0% / 8%        0% / 0%
│   3             60% / 90%+    15% / 25%      0% / 0%
│   4+            100% / 100%   33% / 33%      40% / 40%
│
├─ CRITICAL: Race Fatigue CANNOT occur after Late December.
│   The warning must disappear in the endgame stretch.
│   It must not follow the Trainer into the Climax.
│
├─ Mitigation:
│   Guaranteed Energy/Mood events exist at fixed calendar points:
│   Classic Early Feb (+1 Mood), Classic Early Mar (+20 Energy),
│   Classic Late Sep (+1 Mood), Senior Late Jun (+20 Energy),
│   Senior Late Oct (+1 Mood), Senior Late Dec (+30 Energy).
│
├─ Mood only affects stats by 2% per level.
│   Many races grant Mood via Reporter Events.
│   Source guidance: hold Cupcakes until right before Training,
│   rather than using them reactively after every race.
│
└─ THE TOOL'S ROLE:
    The tool shows the consecutive-race count and the risk band.
    It does NOT simulate the fatigue roll.
    It does NOT recommend whether to race or train.
    It shows the count, the sourced risk table, and the
    Trainer decides. (Planner Rules 1, 4, 5)
```text

#### Flow TB-8: Twinkle Star Climax (The Finale)

```text
REPLACES THE URA-STYLE ELIMINATION FINALS
This is a 3-RACE POINTS LEAGUE, not a bracket.
│
├─ 1. Structure:
│   3 Training turns + 3 Race turns.
│   Same shape as URA's finale (3+3), but the scoring differs.
│
├─ 2. Victory Point scoring:
│
│   Placement     VP
│   ─────────────────
│   1st           10
│   2nd           8
│   3rd           6
│   4th           4
│   5th–6th       3
│   7th–9th       2
│   10th–13th     1
│   14th+         0
│
│   Maximum: 30 VP across 3 races.
│
├─ 3. Winning condition:
│   The Trainer needs the MOST total VP across all 3 races.
│   They do NOT need to win all 3.
│   Winning the aggregate is sufficient.
│   This is fundamentally different from URA's
│   qualifier → semifinal → final elimination.
│
├─ 4. Economy note:
│   Climax races pay NO Shop Coins.
│   The final shop before the Climax is the last chance to spend.
│   The Trainer should save approximately 150 coins for that shop.
│
├─ 5. Item strategy:
│   Save 3 Golden Hammers (Race Bonus items) for the 3 Climax races.
│   These races give less SP than a typical G1,
│   but they grant +10 to all stats at base,
│   making Hammers unusually valuable here.
│   Alternatively, save 2 Hammers if the final shop will offer a 3rd.
│   Source guidance: banking Hammers from Senior Summer onward
│   is the safer default.
│
├─ 6. Reward:
│   Winning the Climax grants hints for the Radiant Star skill.
│   ⚠ This skill name appears in the scenario guide.
│   The tool records what the Trainer reports.
│
└─ 7. Timeline entries appended.
      One entry per Climax race: race name, placement, VP earned,
      running VP total.
      Final entry: overall result, total VP, win/loss.
```text

#### Flow TB-9: Unique Skill Level-Ups (Trackblazer variant)

```text
DIFFERENT FROM URA AND UNITY CUP.
Trackblazer uses an annual "Umamusume of the Year" selection.
│
├─ Timing: Late December each year (Junior, Classic, Senior).
│
├─ Requirements: Fans AND Akikawa bond TOGETHER.
│   (Not fans alone as in URA/Unity Cup.)
│
│   Checkpoint              Fans        Akikawa Bond
│   ─────────────────────────────────────────────────
│   After Late Dec, Junior   5,000      19 (just under 1 full bar)
│   After Late Dec, Classic  60,000     31 (slightly above 1.5 bars)
│   After Late Dec, Senior   120,000    51 (slightly above 2.5 bars)
│
│   ⚠ These fan thresholds differ from URA/Unity Cup.
│   ⚠ The bond requirement is a NEW dimension not present
│     in URA/Unity Cup's April gate.
│
└─ The tool shows BOTH the fan count and the bond level
   as co-requirements. Showing only one would be incomplete.
```text

---

### 2.3 Trackblazer Frontend Specification

#### FC-TB-1: Scenario Resource Strip (replaces baseline strip)

```text
┌──────────────────────────────────────────────────────────────────────┐
│ [ 13 turn(s) left ]  Haru Urara · Trainee · Trackblazer             │
│                                                                      │
│ Energy ▓▓▓▓▓░░░░ 42 [Caution]  │  Fans 1,943 · target 3,000       │
│                                                                      │
│ Grade Points: 47 / 30 (current objective)  │  Deadline: 12 turns    │
│ Shop Coins: 240  │  Shop restocks in: 4 turns                       │
└──────────────────────────────────────────────────────────────────────┘
```text

| Element              | Source                                         | Rendering rule                                                                                          |
| -------------------- | ---------------------------------------------- | ------------------------------------------------------------------------------------------------------- |
| Grade Points         | Trainer-entered after each race                | Progress against the CURRENT objective only. Resets at each deadline. Never shows a cumulative total.   |
| GP Deadline          | Derived from the turn count to Late December   | "N turns" countdown.                                                                                    |
| Shop Coins           | Trainer-entered after each race                | Numeral. Unspent coins die with the run.                                                                |
| Shop restock timer   | Derived from the 6-turn cycle                  | "N turns" countdown. The balance and the timer are both primary numbers.                                |

**What is NOT on the strip:** No Team Rank. No Spirit Burst count. No league rank. These are Unity Cup elements and must not appear (D-220: absent beats empty).

#### FC-TB-2: Grade Point Meter

Replaces the Race Calendar panel entirely. There is no calendar in Trackblazer.

```text
┌──────────────────────────────────────────────────────────────┐
│  GRADE POINTS — Objective 2 of 4                             │
│  ████████████████░░░░░░░░  47 / 60                          │
│  Deadline: End of Junior Year (12 turns remaining)           │
│  Surplus does NOT carry over.                                │
│                                                              │
│  Objective 1: Debut Race  ✓ completed                        │
│  Objective 2: 60 GP       ← current                          │
│  Objective 3: +300 GP     locked                             │
│  Objective 4: +300 GP     locked                             │
└──────────────────────────────────────────────────────────────┘
```text

**Critical rendering rules:**

- The meter shows progress against the CURRENT objective only.
- It resets to zero at each deadline.
- It does NOT show a running total across objectives.
- The "surplus does not carry over" note must be visible.
- The aptitude-dependent threshold (60 vs 30 vs 200) must reflect the trainee's actual aptitude track.

#### FC-TB-3: Special Shop Panel

A collapsible panel accessible from the dashboard. Not always visible; the Trainer opens it when they want to spend.

```text
┌──────────────────────────────────────────────────────────────┐
│  SPECIAL SHOP                                                │
│  Coins: 240  │  Restocks in: 4 turns                        │
│                                                              │
│  [SALE: 15% off]                    [LIMITED: 2 turns left]  │
│                                                              │
│  ┌─────────────────────────────────────────────────────────┐ │
│  │ +15 Speed (30c)          [Buy]  Held: 2/5              │ │
│  │ +7 Stamina (15c)         [Buy]  Held: 0/5              │ │
│  │ +2 Mood (55c)            [Buy]  Held: 1/5              │ │
│  │ +65 Energy (75c)         [Buy]  Held: 0/5              │ │
│  │ +35% Race Bonus (40c)    [Buy]  Held: 3/5              │ │
│  │ +60% Training (70c)      [Buy]  Held: 0/5              │ │
│  │ Whistle (20c)            [Buy]  Held: 1/5              │ │
│  │ Good-Luck Charm (40c)    [Buy]  Held: 0/5              │ │
│  └─────────────────────────────────────────────────────────┘ │
│                                                              │
│  ⚠ Multi-turn items cannot be re-used while active.          │
│  ⚠ Buying a weaker item after a stronger one overwrites it.  │
└──────────────────────────────────────────────────────────────┘
```text

**Rendering rules:**

- Items show cost, effect, and held count (out of 5 max).
- Sale items show the discounted price with the original struck through.
- Limited items show a separate availability timer.
- The overwrite warning is always visible for multi-turn items.
- The shop restock timer is always visible.
- The "spend it" nudge: when coins exceed 200 and restock is within 2 turns, a subtle prompt appears. This is not a recommendation; it is a statement of the economic fact that unspent coins die with the run.

#### FC-TB-4: Epithet Route Tracker

A collapsible list panel, not a grid.

```text
┌──────────────────────────────────────────────────────────────┐
│  EPITHET ROUTES                                              │
│                                                              │
│  ▸ Tiara Route          2 / 3 races won                     │
│    Oka Sho ✓  ·  Japanese Oaks ✓  ·  Shuka Sho —           │
│                                                              │
│  ▸ Classic Route        1 / 3 races won                     │
│    Satsuki Sho ✓  ·  Japanese Derby —  ·  Kikuka Sho —     │
│                                                              │
│  ▸ Sprint/Mile Route    0 / 3 races won                     │
│                                                              │
│  ▸ Dirt Route           7 / 15 wins                         │
│                                                              │
│  Completed:                                                  │
│  ✓ Sprint Go-Getter (+10 to 2 random stats)                 │
└──────────────────────────────────────────────────────────────┘
```text

**Rendering rules:**

- Only routes with at least 1 race won are shown as active.
- Completed routes collapse into a summary line showing the reward.
- The aptitude warning is shown once at the top if the trainee lacks C in Mile or Medium: "⚠ Most graded races are Mile/Medium. Trainee aptitude may limit available routes."
- Secondary epithets (win-count, regional) are collapsed by default.

#### FC-TB-5: Rival Race Indicator

A badge on the race selection button, not a separate panel.

```text
┌──────────────────────────────────────────────────────────────┐
│  [VS] Rival Race Available                                   │
│  Opponent: Silence Suzuka                                    │
│  Race: Tokyo Turf 1600m (Mile)                               │
│  Win reward: Skill hint OR +5 to 2 random stats              │
│  ⚠ Only 1st place counts as a win.                          │
│  ⚠ Requires C aptitude or better in Mile.                   │
└──────────────────────────────────────────────────────────────┘
```text

**Rendering rules:**

- The VS badge uses a red/blue split icon matching the client's speech-bubble mark.
- If the trainee's aptitude in the race distance is below C, the hint reward line is replaced with: "Hint unavailable (aptitude below C)."
- Draw results are recorded distinctly from wins and losses.

#### FC-TB-6: Race Fatigue Chip

A persistent chip in the dashboard, visible only when consecutive-race count ≥ 1.

```text
┌──────────────────────────────────────────────────────────────┐
│  Race Fatigue: 2 consecutive races                           │
│  Risk: Mood Down 33% (at 0 Energy) · Skin Outbreak 8%       │
│  ⚠ Clears after Late December                                │
└──────────────────────────────────────────────────────────────┘
```text

**Rendering rules:**

- Shows the consecutive-race count and the sourced risk percentages.
- The risk percentages are from the sourced table, NOT computed by the tool.
- After Late December, the chip disappears entirely. It does not fade. It does not show "0%." It is gone (D-231).
- The guaranteed Energy/Mood events at fixed calendar points are shown as small markers on the timeline, not on this chip.

#### FC-TB-7: Facility Level Chip (Trackblazer variant)

```text
Speed Lvl 5 ← 4× repetition + 1 purchased
Stamina Lvl 3 ← 3× repetition
```text

The purchased level is always called out separately. A level chip that does not distinguish earned from purchased levels would hide the fact that Trackblazer is the only scenario where facility level is partly bought (D-222).

#### FC-TB-8: Twinkle Star Climax Panel (Finale)

Replaces the URA-style qualifier → semifinal → final progression.

```text
┌──────────────────────────────────────────────────────────────┐
│  TWINKLE STAR CLIMAX                                         │
│  3-Race Points League                                        │
│                                                              │
│  Race 1: 1st place  → 10 VP    ✓ completed                  │
│  Race 2: 3rd place  →  6 VP    ✓ completed                  │
│  Race 3: —          →  — VP    pending                      │
│                                                              │
│  Total VP: 16 / 30                                           │
│  Current standing: 1st of 5                                  │
│                                                              │
│  ⚠ Climax races pay NO Shop Coins.                           │
│  ⚠ Save Hammers for these races (+10 all stats at base).    │
└──────────────────────────────────────────────────────────────┘
```text

**Rendering rules:**

- Shows per-race VP and running total.
- The winning condition is stated: "Most VP wins. You do not need to win all 3."
- This is NOT an elimination bracket. The panel must not render a bracket or knockout structure.
- The "no Shop Coins" warning is visible.
- The Hammer-saving note is a sourced strategy reminder, not a recommendation. It states the economic fact.

#### FC-TB-9: Unique Skill Gate Display (Trackblazer variant)

```text
Umamusume of the Year — Junior
  Fans: 5,000 / 5,000  ✓
  Akikawa Bond: 19 / 19  ✓
  → Unique Skill level up available

Umamusume of the Year — Classic
  Fans: 47,200 / 60,000  ✗
  Akikawa Bond: 28 / 31  ✗
  → Both requirements must be met

Umamusume of the Year — Senior
  Fans: 0 / 120,000  ✗
  Akikawa Bond: 0 / 51  ✗
```text

**Rendering rules:**

- Shows BOTH fans and bond as co-requirements.
- Neither is sufficient alone. The gate is not met until both are reached.
- This differs from URA/Unity Cup (fans only) and must not reuse their single-requirement layout.
- The bond values (19, 31, 51) are sourced from uma.guide. They are displayed as the Trainer enters them.

---

## PART 3: SHARED RULES FOR BOTH SCENARIOS

### 3.1 What the Tool Never Does

These rules apply to both Unity Cup and Trackblazer, and to every scenario:

1. **No simulation.** The tool does not predict race outcomes, training results, or stat trajectories. (Planner Rule 1, PRD §6.11)
2. **No recommendation.** The tool does not suggest which training to pick, which race to enter, or which item to buy. (Planner Rule 5)
3. **No unexplained numbers.** Every figure on screen is either Trainer-entered or a sourced constant with a visible citation. (Planner Rules 4, 5)
4. **No scenario chrome from another scenario.** Unity Cup elements do not appear in Trackblazer. Trackblazer elements do not appear in Unity Cup. Absent beats empty. (D-220, D-221)
5. **No secret-event tracking in Trackblazer.** Secret events do not fire in Trackblazer. The tool must not show a secret-event tracker or imply one exists. (D-221)
6. **No Race Calendar in Trackblazer.** The Race Calendar panel is absent. The Grade Point meter replaces it. (D-221)
7. **No Scenario Link in Trackblazer.** The Scenario Link identity element is absent from the header. (D-216)

### 3.2 Scenario Composition Rule

The dashboard is one spine. The only thing that changes per scenario is which panels the goal region composes (D-220, D-240).

| Panel                    | URA Finale    | Unity Cup                     | Trackblazer                    |
| ------------------------ | ------------- | ----------------------------- | ------------------------------ |
| Turn chip                | Present       | Present                       | Present                        |
| Energy gauge             | Present       | Present                       | Present                        |
| Fan readout              | Present       | Present                       | Present                        |
| Stat band                | Present       | Present                       | Present                        |
| Timeline                 | Present       | Present                       | Present                        |
| Race Calendar            | Present       | Present                       | **ABSENT**                     |
| Grade Point meter        | Absent        | Absent                        | **Present**                    |
| Team Rank gauge          | Absent        | **Present**                   | Absent                         |
| Spirit Burst roster      | Absent        | **Present**                   | Absent                         |
| Team Race schedule       | Absent        | **Present**                   | Absent                         |
| Shop Coin counter        | Absent        | Absent                        | **Present**                    |
| Epithet tracker          | Absent        | Absent                        | **Present**                    |
| Race Fatigue chip        | Absent        | Absent                        | **Present** (when count ≥ 1)   |
| Scenario Link identity   | Aoi Kiryuin   | 5 characters, via Team Name   | **ABSENT**                     |
| Facility level cause     | Repetition    | Team Rank                     | Repetition + Purchased         |

An undescribed scenario (e.g., Our Grand Concert) renders the baseline strip plus its known caps, and nothing else (D-241).

### 3.3 Facility Level Source Labels

Every facility level chip must state its cause. The cause differs per scenario:

| Scenario      | Level chip text                               |
| ------------- | --------------------------------------------- |
| URA Finale    | `Speed Lvl 3 ← 3× repetition`                 |
| Unity Cup     | `Speed Lvl 4 ← Team Rank A`                   |
| Trackblazer   | `Speed Lvl 5 ← 4× repetition + 1 purchased`   |

A level chip without its cause is a review failure (D-222).

### 3.4 Summer Camp (Both Scenarios)

Summer Camp is identical across all scenarios:

- Two windows: Classic Year and Senior Year.
- Each window: 4 turns (Early July to Late August).
- All facilities set to Lv5 simultaneously.
- Rest and Recreation restore both Energy and Mood.
- The camp ends with +5 to 3 random stats.

The tool marks the camp window on the timeline and shows the Lv5 override on all facility tabs. The facility level source label during camp reads: `Lvl 5 ← Summer Camp`.

### 3.5 Timeline Entry Differences

Timeline entries carry scenario-specific fields:

| Field               | URA Finale            | Unity Cup                                         | Trackblazer                                          |
| ------------------- | --------------------- | ------------------------------------------------- | ---------------------------------------------------- |
| Training gains      | Standard              | Standard + Special Training bonus + Burst gains   | Standard                                             |
| Teammate stats      | N/A                   | Per-teammate stat gains, Spirit gauge change      | N/A                                                  |
| Skill hint source   | Event / card pool     | Burst: card's own hint pool (non-random)          | Event / card pool                                    |
| Race result         | Placement, fans, SP   | Placement, fans, SP, Team Race context            | Placement, Grade Points, Shop Coins                  |
| Scenario-specific   | Goal completion       | Team Race result, league rank, Burst count        | Epithet progress, Rival Race result, fatigue count   |

---

## PART 4: COMPONENT STATE SUMMARY

### 4.1 Unity Cup Component States

| Component            | States                                                                                                    |
| -------------------- | --------------------------------------------------------------------------------------------------------- |
| Facility tab         | normal, 1-flame, ≥2-flame, burst-ready, extreme-ready, summer-camp-Lv5                                    |
| Teammate card        | chargeable, charged, held, normal-spent, extreme-chargeable, extreme-spent                                |
| Team Race panel      | countdown, composition, opponent-select, racing, result, retry-available                                  |
| Spirit Burst meter   | filling (0–3), threshold-reached (4–6, 7–9, 10–12, 13+)                                                   |
| Team Name picker     | locked (before event), selectable, confirmed                                                              |
| Unique Skill gate    | not-reached, reached (fan-only for Feb/Dec), reached (fan+bond for April in URA; fan-only in Unity Cup)   |

### 4.2 Trackblazer Component States

| Component             | States                                                                             |
| --------------------- | ---------------------------------------------------------------------------------- |
| Grade Point meter     | objective-1 (debut), objective-2, objective-3, objective-4, completed              |
| Shop panel            | locked (before debut), open, restocking (countdown), sale-active, limited-active   |
| Shop item             | available, held (N/5), active (multi-turn), overwritten, expired                   |
| Epithet route         | hidden (0 races), active (1+), completed, reward-claimed                           |
| Rival Race badge      | hidden, available, won, drawn, lost                                                |
| Race Fatigue chip     | hidden (count 0), visible (count 1+), gone (after Late Dec)                        |
| Climax panel          | locked, race-1, race-2, race-3, completed                                          |
| Facility level chip   | repetition-only, repetition+purchased, summer-camp-Lv5                             |

---

## PART 5: OPEN ITEMS

Items that remain unresolved and must NOT be filled by inference:

| Item                                              | Status                                                                                                                                                                                                 |
| ------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Our Grand Concert mechanics                       | ❌ Not extracted. Baseline + caps only. (D-241)                                                                                                                                                        |
| Unity Cup facility activity names per facility    | ❌ Not captured for Unity Cup specifically.                                                                                                                                                            |
| Trackblazer Debut Race Energy cost                | Sourced as "costs Energy" but exact value not stated.                                                                                                                                                  |
| Radiant Star skill details                        | Name appears in guide; mechanics not extracted.                                                                                                                                                        |
| Trackblazer aptitude exception thresholds         | 30/200/300 and 60/200/300 sourced from uma.guide. Exact aptitude criteria for "high-dirt/low-turf" and "narrow-range turf" not formally defined.                                                       |
| Extreme Spirit Burst bond-gauge bypass            | Whether the ≥80 bond requirement is also bypassed (in addition to the facility-type bypass) is NOT stated by any source. Carry as ❌.                                                                  |
| Trackblazer GP thresholds for the 4th objective   | All tracks converge at +300. Confirmed.                                                                                                                                                                |
| Unity Cup pre-rework values                       | Superseded by the 2026-07-01 rework. Do not use pre-rework numbers. `docs/scenarios/02-unity-cup.md` is superseded by `06-unity-cup-gametora.md` wherever they disagree.                               |
