# UX Behavior Specification: Umamusume Trainer Companion

> **Reviewed, largely sound, not merged — 2026-09-27.** Audit: `docs/design-research/FRONTEND-SPEC-DIVERGENCE.md` §6.
> Three corrections are load-bearing: preferences persist to **SQLite**, not `localStorage` (owner ruling, PRD §6.12;
> see `CONSTRAINTS.md` D-104); the cap-stack example's `+0 breakthrough` is the value DESIGN.md §6.22 forbids
> displaying; and "Run List = default landing" contradicts root DESIGN §4.1, where the catalog index is the desk's
> front page. The universal 4-step turn flow also has to become scenario-composed (`config/scenarios.php` `steps`).
> The input taxonomy (§3) and run-lifecycle state machine (§4) are worth adopting as structure.

Synthesized from `DESIGN.md`, `CONSTRAINTS.md`, `PRD.md`, the ADRs, and the scenario reference files. This is the behavioral contract — what the app does, when, and why.

---

## 1. Global App Behavior (Outside Career Run)

### 1.1 App Shell & Navigation

The app is a **desktop-browser-only** local tool. No auth, no accounts, no server, no sync (PRD §6.1). Everything lives in the browser's storage.

**Navigation structure:**

| Surface           | Purpose                                       | Entry point      |
| ----------------- | --------------------------------------------- | ---------------- |
| Run List          | All recorded career runs, filterable/sortable | Default landing  |
| Run Detail        | One run's full timeline, stats, skills        | Click a run card |
| Guided Turn Input | Log a new turn                                | From Run Detail  |
| Skill Search      | Find skills by normalized key                 | Global nav       |
| Catalog           | Umamusume roster with aptitudes               | Global nav       |
| Settings          | Theme, display timezone, config               | Global nav       |

**The shell must:**

- Apply the selected theme **before first paint** (inline `<script>` in `<head>`, no flash of wrong theme — CONSTRAINTS D-104)
- Default to **light theme** (the client is a high-key interface — DESIGN §3.7)
- Support dark theme as a token override, not a fork (D-101)
- Persist theme choice to `localStorage`, initialize from `prefers-color-scheme` (D-104)
- Display all dates through `config('uma.display_timezone')` (D-34)

### 1.2 Run List (Screen C)

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

### 1.3 Skill Search (Screen D)

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

### 1.4 Catalog

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

### 1.5 Settings

**Available toggles:**

| Setting                  | Options               | Default            | Persistence    |
| ------------------------ | --------------------- | ------------------ | -------------- |
| Theme                    | Light / Dark / System | System             | `localStorage` |
| Display timezone         | IANA timezone list    | Browser default    | `localStorage` |
| Numeric failure estimate | Off / On              | **Off** (ADR-0001) | `localStorage` |

**The numeric failure estimate toggle** is the only config-gated feature (ADR-0001 §3). When enabled, it must render with its formula and parameters visible on the same surface, labeled as "this tool's model" rather than the game's (D-155).

### 1.6 Global Constraints on All Inputs

These apply everywhere, always:

| Rule                                                | Source          |
| --------------------------------------------------- | --------------- |
| No simulation, prediction, or race-day snapshots    | PRD §6.11       |
| No support-card database in Phase 1                 | PRD §6.9        |
| No event/banner calendar in Phase 1                 | PRD §6.6        |
| No trainee image upload                             | PRD §6.13       |
| No localStorage-authored runs                       | PRD §6.12       |
| Every number must be explainable from entered turns | Planner Rule 4  |
| No unexplained recommended numbers                  | Planner Rule 5  |
| No equine vocabulary anywhere                       | CONSTRAINTS C-4 |
| Sentence case in all UI copy                        | CONSTRAINTS C-4 |
| No decorative emoji in labels                       | DESIGN §6.0     |

---

## 2. During a Career Run

### 2.1 The Persistent Dashboard (Screen A)

This is the **always-visible frame** during a run. It never scrolls away.

**Layout: two regions**

| Region | Contains                                                      | Behavior                     |
| ------ | ------------------------------------------------------------- | ---------------------------- |
| Left   | Run identity, turn chip, stat band, Energy gauge, fan readout | Persistent, never collapses  |
| Right  | Turn timeline                                                 | Scrollable, grouped by phase |

**The persistent resource strip** (D-170, D-230):

```
[ 13 turn(s) left ]  Rice Shower · Trainee Umamusume · Unity Cup
                     Energy ▓▓▓▓▓░░░░  42  [Caution]   |   1,943 fans · target 3,000
```

This strip is **scenario-composed** (D-220, D-240). The base items (turn, trainee, scenario, Energy, fans) are always present. Scenario-specific items appear only when the scenario has them:

| Scenario          | Additional strip items                          |
| ----------------- | ----------------------------------------------- |
| URA Finale        | None (baseline)                                 |
| Unity Cup         | Team Rank, Spirit Burst count                   |
| Trackblazer       | Grade Points (vs current objective), Shop Coins |
| Our Grand Concert | Baseline only (mechanics unextracted — D-241)   |

**Absent beats empty.** A URA run never shows an empty Team Rank slot. A Trackblazer run never shows a Race Calendar panel.

### 2.2 The Turn Chip

**Behavior:**

- Torn-page calendar card (DESIGN §6.6)
- Shows: remaining turn count at `numeral-xl` in `blue-700`, phase countdown beneath
- **First in reading order and DOM** (D-42)
- Never hardcodes a total turn count (D-136) — shows turn number and turns-left only

**The turn chip is the orientation anchor.** A Trainer scanning the screen finds "where am I" before anything else.

### 2.3 The Stat Band

**Behavior:**

- Six columns: Speed, Stamina, Power, Guts, Wit, Skill Points
- Each column: grade badge + `numeral-lg` value + `/cap` at `meta`
- **The value is the largest thing in the band. The label is not.** (P3)
- Subtle per-column tint from §3.6 table (near-invisible individually, reads as a group)

**The cap display is a stack, not a number** (D-162):

```
Wit  1,340 / 1,800
     1,200 base · +600 scenario · +0 breakthrough · deck not tracked
```

The denominator is the sum of terms the tool actually holds. Each component is labeled. **The disclosure line names what is included and what is not.** (D-162)

**Two markers, two meanings** (DESIGN §6.5, D-211):

- **1200 line**: the halved-gains threshold — a game mechanic, drawn as a dashed tick
- **Scenario ceiling**: the maximum holdable value, drawn as the bar end

They are different facts and must not collapse into one line.

### 2.4 The Energy Gauge

**Behavior:**

- Fully rounded track, segmented fill sweeping cyan → green → lime → amber → red (DESIGN §6.15)
- Color encodes **position on the bar**, not the current level
- Label "Energy" in its own small pill to the left
- Value at `numeral-md` at the right end, tabular
- **2px `--color-risk` rule across the track at the 50 line**, always visible

**Band word** (not color alone — D-12):

| Band    | Range | Word    | Treatment              |
| ------- | ----- | ------- | ---------------------- |
| Safe    | > 50  | Safe    | `ink` on `green-tint`  |
| Caution | 30–50 | Caution | `ink-strong` on `gold` |
| Danger  | < 30  | Danger  | white on `risk`        |

**The 30 boundary is an owner ruling, not a game fact.** No source publishes a threshold below 50 (ADR-0001 §3). Where the Danger band is shown, it must be attributable to the tool's own model.

### 2.5 The Turn Timeline

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

### 2.6 Guided Turn Input (Screen B)

This is where the brief's "not a web form" requirement lands.

**Behavior:**

- A turn is logged as a **sequence of decision cards**, each asking a single question in the game's voice (DESIGN §6.10)
- **One question per step**, in the client's decision order: training → outcome → skill event → note (D-52)
- **Preview before commit, always** (D-51, P2)
- **Committing is a separate explicit action** — selecting never writes

**The flow:**

```
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
```

**Critical: no numbered step sidebar.** A vertical 1-2-3-4 rail is a SaaS setup wizard and stops feeling like an in-game event (D-117). The flow carries a single `Step 1 of 3` line with three dots at the foot of the card stack, and nothing else.

**The preview is the input.** The Trainer is not filling in six number fields and hoping; they are picking the thing that produced the numbers they can see.

**Escape hatch:** direct field access exists for paste-and-correct, but it is secondary, not the default path (D-53).

**Keyboard-first:** 1-5 select a discipline, Enter confirms, Escape steps back. The full flow is completable without a pointer (D-55).

### 2.7 The Five Training Options

Each is a **banner button** (DESIGN §6.1):

- White body, 1px accent outline, radius 10 on the left end only
- Right end terminates in an arrow cap: 40px gradient wedge pointing right
- Left end carries a 28px circular accent icon
- `ink-strong` label

**Selected state:** the whole body fills with `green` and the label goes white, or fills `gold` with `ink-strong`.

**What each option shows in preview:**

| Training | Preview shows                                                 |
| -------- | ------------------------------------------------------------- |
| Speed    | Speed gain, Power gain, SP gain, Energy cost                  |
| Stamina  | Stamina gain, Guts gain, SP gain, Energy cost                 |
| Power    | Power gain, Stamina gain, SP gain, Energy cost                |
| Guts     | Guts gain, Speed gain, Power gain, SP gain, Energy cost       |
| Wit      | Wit gain, Speed gain, SP gain, **Energy recovery** (not cost) |
| Rest     | +30 Energy recovery                                           |

**When Energy is below 50**, the advisory row appears (DESIGN §6.16):

```
Wit costs 0 Energy and you are at 42. (GameWith 2026-09-25; source data 2023-02-25)
```

The advisory **suggests; it never disables, dims, or reorders** the five options (D-155). The Trainer picks; the tool explains.

### 2.8 Race Entry (F5)

**Appears only on a turn where the calendar holds an entry** (D-175). On turns with no race, the flow goes straight to training or rest.

**Behavior:**

- Reuses the event-panel shape (banner buttons, arrow-caps, preview-before-commit)
- Each race option shows: race name, tier badge, `fans_needed` vs current fan total, whether mandatory, Energy cost
- **Fan-locked races render disabled with their number, never hidden** (D-174)

**The panel must not rank races, project a placing, or estimate a win** (D-155). Eligibility is arithmetic over stored values and is fine. Outcome is not, and does not appear.

**Skip is a first-class option**, rendered as a banner like any other (D-176). "I am choosing not to run this" is a decision the Trainer makes deliberately.

### 2.9 Scenario-Specific Panels

**Unity Cup additions:**

| Panel               | Behavior                                                                                                                                                      |
| ------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Team Rank gauge     | Letter per stat + aggregate rank. Facility level derives from per-stat rank. "It's On!" reward derives from aggregate. Two different consumers of one widget. |
| Spirit Burst roster | Compact list of teammates, each carrying one of six states: chargeable, charged, held, normal-spent, Extreme-chargeable, Extreme-spent                        |
| Team Race countdown | Under the objective timer, "N turns until next Team Race"                                                                                                     |

**Spirit Burst state machine** (DESIGN §6.24, D-223):

```
chargeable → charged → held → normal spent → Extreme chargeable → Extreme spent
```

**A spent-normal teammate is not inert.** They are the next Extreme candidate. Any "consumed" treatment (reduced opacity, strikethrough) is a lie on the fifth state.

**Trackblazer additions:**

| Panel             | Behavior                                                                                                                                                                                |
| ----------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Grade Point meter | Progress against **current objective only**, with deadline date and "surplus does not carry over" note. A cumulative total would advertise a banking strategy the rules forbid (D-232). |
| Shop Coin counter | Balance + turns until rotation. The balance is the lesser of the two.                                                                                                                   |
| Epithet tracker   | Won-of-needed per route. Keep as a list, not a grid.                                                                                                                                    |
| Race Fatigue chip | Consecutive-race count + consequence. **Vanishes after Late December** (D-230, D-231).                                                                                                  |

**No Race Calendar in Trackblazer.** Its absence is the scenario's defining UI fact (D-221).

### 2.10 Event Resolution (F2, F3, F4)

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

### 2.11 The Race Calendar (URA, Unity Cup only)

**Applies only to scenarios with mandatory race goals** (D-221).

**Behavior:**

- 24-cell half-month grid under a Junior/Classic/Senior year tab bar (DESIGN §6.20)
- Cell states (the count is five because there are genuinely five things a Trainer needs to distinguish):

| State                      | Treatment                                                                   | Basis    |
| -------------------------- | --------------------------------------------------------------------------- | -------- |
| Empty half-month           | `sunken` fill, muted plus glyph                                             | Measured |
| Optional race, enterable   | `raised` fill, green plus, race artwork thumbnail                           | Measured |
| Mandatory Goal race        | Red `Goal` pennant on cell corner, warm outline                             | Measured |
| Scheduled, already entered | Pink `Scheduled` pill over thumbnail                                        | Measured |
| Locked by fans             | Dimmed, padlock + exact `fans_needed` figure                                | Derived  |
| Locked by maiden gate      | Dimmed, dashed outline, distinct marker, "Win Debut or a Maiden race first" | Derived  |
| Current turn               | Pale yellow fill with warm outline                                          | Measured |

**Lock state grammar — the three that matter:**

| State          | Fill                                      | Marker                                   | Text                               | Why distinct                                                                          |
| -------------- | ----------------------------------------- | ---------------------------------------- | ---------------------------------- | ------------------------------------------------------------------------------------- |
| Mandatory Goal | `raised`, warm 2px outline                | Red `Goal` pennant                       | Race name at `label-strong`        | Not a lock. An obligation. Reads heavier, never dimmer.                               |
| Fan-locked     | `sunken`, 55% opacity                     | Padlock glyph                            | `1,000 fans` as a number           | Remedy is measurable. Trainer can see shortfall against header total.                 |
| Maiden-locked  | `sunken`, 55% opacity, **dashed** outline | Distinct conditional marker, not padlock | "Win Debut or a Maiden race first" | Remedy is an event, not a quantity. Padlock would send Trainer to check wrong number. |

**The dashed outline on the maiden lock is the whole difference** — opacity and a padlock already say "you lack a number," so the conditional gate must break the pattern rather than join it.

**Completed and entered races** use the client's own pink `Scheduled` pill and are never dimmed.

### 2.12 Run Completion

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

## 3. Input Taxonomy

### 3.1 All User Inputs

| Input type                  | Where used                                                 | Behavior                                                    |
| --------------------------- | ---------------------------------------------------------- | ----------------------------------------------------------- |
| **Banner button selection** | Training options, race options, event choices              | Select shows preview; commit is separate                    |
| **Numeric entry**           | Outcome numbers, fan count, Energy, stats                  | Direct field access (escape hatch), paste-and-correct       |
| **Free text**               | Notes, run name                                            | Optional, never required                                    |
| **Dropdown**                | Sort order, scenario filter                                | Single-select, immediate effect                             |
| **Toggle**                  | Theme, numeric failure estimate                            | Immediate effect, persisted to `localStorage`               |
| **Search field**            | Skill search, catalog search                               | Debounced, cancellable, never fires network request (NFR-1) |
| **Keyboard**                | 1-5 for disciplines, Enter to confirm, Escape to step back | Full flow completable without pointer (D-55)                |

### 3.2 All Options

| Option                   | Values                                 | Source                         |
| ------------------------ | -------------------------------------- | ------------------------------ |
| Training discipline      | Speed, Stamina, Power, Guts, Wit, Rest | Game's five disciplines + Rest |
| Race selection           | Available races from calendar          | Scenario calendar data         |
| Event choice             | A/B/C (typically 2-3 options)          | Game event structure           |
| Sort order               | Date, turn depth, trainee name         | Run List                       |
| Filter                   | Scenario, status                       | Run List                       |
| Theme                    | Light, Dark, System                    | Settings                       |
| Numeric failure estimate | Off, On                                | Settings (ADR-0001)            |

### 3.3 All Toggles

| Toggle                   | Location | Default                | Effect                                                                      |
| ------------------------ | -------- | ---------------------- | --------------------------------------------------------------------------- |
| Theme                    | Settings | System                 | Switches token override before first paint                                  |
| Numeric failure estimate | Settings | **Off**                | When on, shows percentage with formula visible, labeled "this tool's model" |
| Phase collapse           | Timeline | Current phase expanded | Collapses/expands phase groups                                              |

### 3.4 What Is NOT an Input

These are explicitly **not user inputs** in this tool:

| Not an input               | Why                                                    | Source |
| -------------------------- | ------------------------------------------------------ | ------ |
| Race outcome prediction    | PRD §6.11 — no simulation                              |        |
| Stat projection            | Planner Rule 4 — deterministic over entered turns only |        |
| Deck composition           | PRD §6.9 — no support-card database in Phase 1         |        |
| Event calendar             | PRD §6.6 — no event/banner calendar in Phase 1         |        |
| Trainee image              | PRD §6.13 — no image upload                            |        |
| Career Rank derivation     | Planner Rule 1 — no simulation; entered only           |        |
| Scenario selection mid-run | Scenario is fixed at run creation                      |        |

---

## 4. State Machine: The Run Lifecycle

```
[No Run] → [Active] → [Completed] or [Retired]
              ↑
              └── turns logged one at a time
```

**States:**

| State     | Meaning                             | Allowed actions                         |
| --------- | ----------------------------------- | --------------------------------------- |
| Active    | Run in progress, turns being logged | Log turn, view timeline, search skills  |
| Completed | Final turn logged, summary shown    | View summary, view timeline (read-only) |
| Retired   | Run ended early (failed objectives) | View summary, view timeline (read-only) |

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

## 5. Error, Empty, Loading States (Mandatory on Every Data View)

Per root `CONSTRAINTS.md` C-7 and PRD `ARCHITECTURE.md` §7, every data region implements all four states:

| State             | Treatment                                                                                                                                                                  |
| ----------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Empty             | Panel with capsule header, quiet illustration slot, `title` line naming what belongs here, `body` line saying how to make one, one primary button. Never a bare "No data". |
| Loading           | Panel's real shape with `idle`-filled skeleton rows. The client never shows a spinner over a blank page; it shows the frame you are about to fill.                         |
| Refresh in flight | Catalog reads show last-fetched time + quiet progress mark (stale-while-revalidate). UI must not imply data vanished.                                                      |
| Error             | `crimson` rule on left of panel, icon, failed source's name, retry button. Failed fetch must never blank the catalog (NFR-2).                                              |

**The empty state is a designed screen, not a gap.** It is the first screen a Trainer ever sees.

---

## 6. Motion Contract

| Interaction        | Spec                                                                             |
| ------------------ | -------------------------------------------------------------------------------- |
| Button press       | 120ms, translateY 1px, shadow lift to press                                      |
| Card select        | 140ms ease-out, glow ring fades in, fill crossfades                              |
| Choice card expand | 180ms, preview slides 8px and fades; card does not move                          |
| Commit             | 200ms; new turn entry pushes in from bottom of timeline, gain bubble fires first |
| Panel open         | 160ms ease-out, scale 0.98 to 1 plus fade. No slide-in sheets.                   |
| Phase collapse     | 180ms height, 120ms chevron rotate                                               |
| Number change      | Count-up over 240ms, only on the stat band                                       |

**Rules:**

- Nothing animates that is not responding to the Trainer (no idle motion, no ambient drift, no entrance animation on page load)
- No motion longer than 240ms
- `prefers-reduced-motion: reduce` collapses every transition to a 1ms crossfade, disables count-up, keeps state change legible without movement (D-92)
- Motion never carries information alone — a state change must be legible with animation disabled (D-93)

---

## 7. Accessibility Contract

| Requirement          | Implementation                                                                                 |
| -------------------- | ---------------------------------------------------------------------------------------------- |
| Contrast             | Every text pair passes WCAG 2.1 AA at its size (§3.4). Chrome steps never carry text.          |
| Focus                | 3px `green` ring at 35% alpha, always visible, never `outline: none` without replacement       |
| Target size          | 44px minimum on anything clickable                                                             |
| Color independence   | Every state carries a word. Grade badges show the letter. Deltas show sign and direction word. |
| Keyboard             | Guided flow fully operable without pointer. Turn timeline is a list with roving focus.         |
| Screen readers       | Stat band is a table or list with explicit labels. Gain bubbles are `aria-live="polite"`.      |
| Decorative exclusion | Faceted page field and lattice bleed are decorative CSS, not exposed to assistive technology.  |

---

## 8. What This App Never Does

| Never                                            | Source           |
| ------------------------------------------------ | ---------------- |
| Predict race outcomes                            | PRD §6.11        |
| Simulate training results                        | PRD §6.11        |
| Store support-card decks                         | PRD §6.9         |
| Show event/banner calendars                      | PRD §6.6         |
| Accept trainee images                            | PRD §6.13        |
| Author runs in localStorage                      | PRD §6.12        |
| Require auth or accounts                         | PRD §6.1         |
| Use equine vocabulary                            | CONSTRAINTS C-4  |
| Show unexplained recommended numbers             | Planner Rule 5   |
| Derive Career Rank or Rating                     | Planner Rule 1   |
| Display a race-day snapshot                      | PRD §6.11        |
| Invent scenario chrome for undescribed scenarios | D-241            |
| Merge two servers' data                          | Reference doc §1 |
| Promote unverified items into UI copy            | D-20             |

---

This is the behavioral contract. Every input, option, toggle, state, and transition is sourced from the design documents. Where a behavior is not specified here, it is either out of Phase 1 scope or has not been decided — and the absence is deliberate, not an omission.
