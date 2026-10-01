# Scenario-Specific User Flows & Frontend Specifications

> **Reviewed, largely sound, not merged — 2026-09-27.** Audit: `docs/design-research/FRONTEND-SPEC-DIVERGENCE.md` §6.
> Most of this traces to the repo's sourced tables (Trackblazer caps in the corrected order, shop prices, the
> 150-coin permanent facility raise, energy bands with the "30 is ours, not the game's" caveat). Fix before use: the
> Race Fatigue chip prints single-source percentages as bare numbers (D-230), the resource strip carries more widgets
> than §6.23's zero-to-five cap, "3+ of 5 to win" misreads a win-odds estimate as a victory rule, "Anklet" is
> Ankle Weights, and "inheritance" as the system noun should be Inspiration / Legacy Select.

## Unity Cup and Trackblazer

All specifications below are grounded in `UMAMUSUME_REFERENCE.md`, `docs/scenarios/02-unity-cup.md`, `docs/scenarios/04-trackblazer-umaguide.md`, `docs/scenarios/05-trackblazer-gametora.md`, `docs/scenarios/06-unity-cup-gametora.md`, `SCENARIO-DIFFERENCES.md`, and `DESIGN.md`. Nothing here simulates, predicts, or recommends (PRD §6.11, Planner Rules 1–5). Every number rendered is Trainer-entered or a sourced constant.

---

# PART 1: UNITY CUP

## 1.1 Scenario Identity

Unity Cup transforms the solo training loop into a team-management problem. The Trainer is no longer optimizing one Umamusume in isolation; they are building a roster, managing Spirit Burst charges across teammates, and fielding sub-teams for periodic Team Races. The facility level system is entirely replaced: personal repetition no longer matters; the team's aggregate stat rank determines everything.

**What changes from the baseline (URA Finale):**

| Axis                         | URA Finale                | Unity Cup                          |
| ---------------------------- | ------------------------- | ---------------------------------- |
| Facility level driver        | Repeat one stat 4×        | Team aggregate stat rank per stat  |
| Scenario currency            | None                      | Team Rank, Spirit Burst count      |
| Periodic event               | None                      | Team Race every 6 months (5 races) |
| Scenario Link                | Aoi Kiryuin (1 character) | 5 characters, chosen via Team Name |
| Acting chairman              | Akikawa                   | Riko Kashimoto (Akikawa absent)    |
| Stat caps                    | 1400 × 5                  | 1300 × 4, Wit 1800                 |
| April Unique Skill bond gate | 3-bar Akikawa friendship  | No bond condition (Akikawa absent) |

**What does NOT change:** the five training disciplines, Energy/Mood system, race mechanics, skill acquisition, inheritance, Summer Camp (both years, 4 turns, all facilities Lv5).

---

## 1.2 User Flow: Team Composition & Spirit Burst Management

This is the core loop unique to Unity Cup. It runs continuously alongside normal training turns.

### Flow UC-1: Special Training Decision

```
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
```

### Flow UC-2: Spirit Burst Resolution

```
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
```

### Flow UC-3: Team Race (every 6 months)

```
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
```

### Flow UC-4: Team Name Selection (Junior Year, 2nd half September)

```
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
```

### Flow UC-5: Scenario Skill Ladder

```
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
```

### Flow UC-6: Unity Cup Finals

```
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
```

### Flow UC-7: Unique Skill Level-Ups (Unity Cup variant)

```
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
```

---

## 1.3 Unity Cup Frontend Specification

### FC-UC-1: Scenario Resource Strip (replaces baseline strip)

The persistent header strip gains Unity Cup-specific elements.

```
┌──────────────────────────────────────────────────────────────────────┐
│ [ 13 turn(s) left ]  Rice Shower · Trainee · Unity Cup              │
│                                                                      │
│ Energy ▓▓▓▓▓░░░░ 42 [Caution]  │  Fans 1,943 · target 3,000       │
│                                                                      │
│ Team Rank: B  │  Spirit Bursts: 4/13+  │  League Rank: 7            │
│ Next Team Race: 3 turns                                              │
└──────────────────────────────────────────────────────────────────────┘
```

| Element                  | Source                                           | Rendering rule                                                                                              |
| ------------------------ | ------------------------------------------------ | ----------------------------------------------------------------------------------------------------------- |
| Team Rank                | Trainer-entered after each rank-up               | Letter chip (G through S+). Uses the grade badge palette from §6.7. S+ gets a distinct visual step above S. |
| Spirit Burst count       | Running total of normal + Extreme Bursts         | Fraction display: `current / next-threshold`. The denominator changes at each ladder rung (6, 9, 12, 13+).  |
| League Rank              | Trainer-entered after each Team Race             | Numeral. Rises with opponent strength, falls on loss.                                                       |
| Next Team Race countdown | Derived from turn count and the 6-month interval | "N turns" label. Sits under the objective timer, matching the client's placement.                           |

**What is NOT on the strip:** Energy, Fans, turn chip, and scenario name remain identical to the baseline. The strip is composed, not forked (D-220).

### FC-UC-2: Facility Tab Modification

Each facility tab gains two additions over the baseline:

**a) Occupancy display.** Teammates present on the tile are shown as small circular avatars. Each chargeable teammate carries a white flame icon. The flame count is the primary signal.

| Flame count   | Visual treatment                                 | Meaning                                                                           |
| ------------- | ------------------------------------------------ | --------------------------------------------------------------------------------- |
| 0             | No flames. Standard tile.                        | Normal training. No team benefit.                                                 |
| 1             | One flame. Subtle.                               | Benefits that teammate only. No trainee bonus. The tile should NOT glow or pulse. |
| ≥2            | Multiple flames. The tile gets a warm highlight. | Trainee gains bonus stats + SP. This is the actionable state.                     |
| Burst-ready   | Flame icon gains a filled gauge ring.            | Spirit Burst will trigger on this tile if the Trainer confirms.                   |
| Extreme-ready | Flame icon gains a purple ring.                  | Extreme Spirit Burst available. Failure risk for this facility is 0%.             |

**b) Facility level source label.** The level chip must state its cause:

```
Speed Lvl 4 ← Team Rank A
```

Not just `Lvl 4`. In URA Finale, the cause is repetition. In Unity Cup, it is always the team rank. A level chip without its cause is misleading (D-222).

### FC-UC-3: Teammate Roster Panel

A collapsible panel on the right side of the dashboard, below the timeline.

| Column          | Content                                                                                     |
| --------------- | ------------------------------------------------------------------------------------------- |
| Teammate name   | From support card, story character, or random recruit                                       |
| Type badge      | Support card type (Speed/Stamina/Power/Guts/Wit/Pal/Group)                                  |
| Spirit gauge    | 0–100 fill bar. Orange at full (burst-ready).                                               |
| Burst state     | One of 6 states: chargeable, charged, held, normal-spent, Extreme-chargeable, Extreme-spent |
| Stats           | Teammate's own Speed/Stamina/Power/Guts/Wit (raised only through Special Training)          |
| Scenario-linked | Green pill if the teammate is one of the 5 linked characters                                |

**State machine rendering:**

```
chargeable ──→ charged ──→ held ──→ normal spent ──→ Extreme chargeable ──→ Extreme spent
   (white)     (filled)   (deliberately              (NOT a dead end;       (final state
                gauge       untriggered)               purple ring)           for this run)
                full)
```

A "spent" teammate must NEVER render as consumed or resettable. They are one tier spent, one tier pending (D-223).

### FC-UC-4: Team Race Composition Screen

Full-width overlay, triggered when the Team Race countdown reaches zero.

```
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
```

Circle indicators: ○ = disadvantaged, ● = favored. These are the GAME's own estimates, recorded by the Trainer. The tool does not compute them.

**Post-race state:** If lost, an `Alarm Clock retry` button appears. Pressing it supersedes the loss entry in the timeline. The original loss is NOT deleted; it is marked as superseded.

### FC-UC-5: Scenario Skill Progress Meter

A horizontal progress bar in the dashboard, below the resource strip.

```
Spirit Burst Progress
████████████████░░░░  11 of 13+
Next reward: Gold hint Lv1, +30 [highest team stat], +30 SP
```

The denominator shifts at each threshold (6 → 9 → 12 → 13+). The bar must reflect the CURRENT threshold, not a fixed maximum.

The matching stat is determined by the team's highest stat rank. This is displayed as text, not assumed.

### FC-UC-6: Unique Skill Gate Display (Unity Cup variant)

Same three gates as URA Finale, but the April gate omits the bond requirement:

```
Valentine's Gate   60,000 / 40,000 fans   [ reached / not reached ]
April Gate         70,000 / 60,000 fans   [ reached / not reached ]
                   (no bond requirement in Unity Cup)
Christmas Gate     120,000 / 80,000 fans  [ reached / not reached ]
```

The tool must NOT render an Akikawa bond bar in the April gate for Unity Cup runs. Rendering it would state a requirement that does not exist in this scenario (Akikawa is absent; Riko Kashimoto stands in).

---

# PART 2: TRACKBLAZER

## 2.1 Scenario Identity

Trackblazer inverts the relationship between training and racing. In URA Finale and Unity Cup, racing serves training (fans, skill points, objectives). In Trackblazer, training serves racing. The scenario has no mandatory race goals. Instead, the Trainer accumulates Grade Points through racing to meet deadlines, earns Shop Coins through race placement, and spends them in a rotating shop. The finale is a points league, not an elimination bracket.

**What changes from the baseline:**

| Axis              | URA Finale                                 | Trackblazer                                          |
| ----------------- | ------------------------------------------ | ---------------------------------------------------- |
| Goal structure    | Fixed mandatory race goals                 | Grade Point deadlines (no race goals)                |
| Scenario currency | None                                       | Grade Points, Shop Coins                             |
| Shop              | None                                       | In-run shop, restocks every 6 turns                  |
| Facility level    | Repeat 4×                                  | Repeat 4× PLUS permanent +1 from shop                |
| Scenario Link     | Aoi Kiryuin (1)                            | None (the only scenario with zero linked characters) |
| Secret events     | Yes                                        | Do NOT trigger                                       |
| Epithets          | None                                       | Race route bonuses (stat + hint rewards)             |
| Rival Races       | None                                       | From Early August Junior Year                        |
| Finale            | URA elimination (qualifier → semi → final) | Twinkle Star Climax: 3-race points league            |
| Stat caps         | 1400 × 5                                   | 1200 / 1900 / 1200 / 1200 / 1500                     |

**What does NOT change:** the five training disciplines, Energy/Mood system, race mechanics, skill acquisition, inheritance, Summer Camp.

**What is explicitly absent:** No Race Calendar panel. No mandatory race goals. No Scenario Link. No secret character events. These absences are the scenario's defining UI facts (D-221).

---

## 2.2 User Flow: Grade Point Objectives

### Flow TB-1: Grade Point Tracking

```
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
```

### Flow TB-2: Grade Point Earning (through racing)

```
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
```

### Flow TB-3: Shop Coins & The Special Shop

```
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
```

### Flow TB-4: Epithet Route Tracking

```
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
```

### Flow TB-5: Rival Races

```
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
```

### Flow TB-6: Facility Level (Trackblazer variant)

```
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
```

### Flow TB-7: Race Fatigue Management

```
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
```

### Flow TB-8: Twinkle Star Climax (The Finale)

```
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
```

### Flow TB-9: Unique Skill Level-Ups (Trackblazer variant)

```
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
```

---

## 2.3 Trackblazer Frontend Specification

### FC-TB-1: Scenario Resource Strip (replaces baseline strip)

```
┌──────────────────────────────────────────────────────────────────────┐
│ [ 13 turn(s) left ]  Haru Urara · Trainee · Trackblazer             │
│                                                                      │
│ Energy ▓▓▓▓▓░░░░ 42 [Caution]  │  Fans 1,943 · target 3,000       │
│                                                                      │
│ Grade Points: 47 / 30 (current objective)  │  Deadline: 12 turns    │
│ Shop Coins: 240  │  Shop restocks in: 4 turns                       │
└──────────────────────────────────────────────────────────────────────┘
```

| Element            | Source                                       | Rendering rule                                                                                        |
| ------------------ | -------------------------------------------- | ----------------------------------------------------------------------------------------------------- |
| Grade Points       | Trainer-entered after each race              | Progress against the CURRENT objective only. Resets at each deadline. Never shows a cumulative total. |
| GP Deadline        | Derived from the turn count to Late December | "N turns" countdown.                                                                                  |
| Shop Coins         | Trainer-entered after each race              | Numeral. Unspent coins die with the run.                                                              |
| Shop restock timer | Derived from the 6-turn cycle                | "N turns" countdown. The balance and the timer are both primary numbers.                              |

**What is NOT on the strip:** No Team Rank. No Spirit Burst count. No league rank. These are Unity Cup elements and must not appear (D-220: absent beats empty).

### FC-TB-2: Grade Point Meter

Replaces the Race Calendar panel entirely. There is no calendar in Trackblazer.

```
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
```

**Critical rendering rules:**

- The meter shows progress against the CURRENT objective only.
- It resets to zero at each deadline.
- It does NOT show a running total across objectives.
- The "surplus does not carry over" note must be visible.
- The aptitude-dependent threshold (60 vs 30 vs 200) must reflect the trainee's actual aptitude track.

### FC-TB-3: Special Shop Panel

A collapsible panel accessible from the dashboard. Not always visible; the Trainer opens it when they want to spend.

```
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
```

**Rendering rules:**

- Items show cost, effect, and held count (out of 5 max).
- Sale items show the discounted price with the original struck through.
- Limited items show a separate availability timer.
- The overwrite warning is always visible for multi-turn items.
- The shop restock timer is always visible.
- The "spend it" nudge: when coins exceed 200 and restock is within 2 turns, a subtle prompt appears. This is not a recommendation; it is a statement of the economic fact that unspent coins die with the run.

### FC-TB-4: Epithet Route Tracker

A collapsible list panel, not a grid.

```
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
```

**Rendering rules:**

- Only routes with at least 1 race won are shown as active.
- Completed routes collapse into a summary line showing the reward.
- The aptitude warning is shown once at the top if the trainee lacks C in Mile or Medium: "⚠ Most graded races are Mile/Medium. Trainee aptitude may limit available routes."
- Secondary epithets (win-count, regional) are collapsed by default.

### FC-TB-5: Rival Race Indicator

A badge on the race selection button, not a separate panel.

```
┌──────────────────────────────────────────────────────────────┐
│  [VS] Rival Race Available                                   │
│  Opponent: Silence Suzuka                                    │
│  Race: Tokyo Turf 1600m (Mile)                               │
│  Win reward: Skill hint OR +5 to 2 random stats              │
│  ⚠ Only 1st place counts as a win.                          │
│  ⚠ Requires C aptitude or better in Mile.                   │
└──────────────────────────────────────────────────────────────┘
```

**Rendering rules:**

- The VS badge uses a red/blue split icon matching the client's speech-bubble mark.
- If the trainee's aptitude in the race distance is below C, the hint reward line is replaced with: "Hint unavailable (aptitude below C)."
- Draw results are recorded distinctly from wins and losses.

### FC-TB-6: Race Fatigue Chip

A persistent chip in the dashboard, visible only when consecutive-race count ≥ 1.

```
┌──────────────────────────────────────────────────────────────┐
│  Race Fatigue: 2 consecutive races                           │
│  Risk: Mood Down 33% (at 0 Energy) · Skin Outbreak 8%       │
│  ⚠ Clears after Late December                                │
└──────────────────────────────────────────────────────────────┘
```

**Rendering rules:**

- Shows the consecutive-race count and the sourced risk percentages.
- The risk percentages are from the sourced table, NOT computed by the tool.
- After Late December, the chip disappears entirely. It does not fade. It does not show "0%." It is gone (D-231).
- The guaranteed Energy/Mood events at fixed calendar points are shown as small markers on the timeline, not on this chip.

### FC-TB-7: Facility Level Chip (Trackblazer variant)

```
Speed Lvl 5 ← 4× repetition + 1 purchased
Stamina Lvl 3 ← 3× repetition
```

The purchased level is always called out separately. A level chip that does not distinguish earned from purchased levels would hide the fact that Trackblazer is the only scenario where facility level is partly bought (D-222).

### FC-TB-8: Twinkle Star Climax Panel (Finale)

Replaces the URA-style qualifier → semifinal → final progression.

```
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
```

**Rendering rules:**

- Shows per-race VP and running total.
- The winning condition is stated: "Most VP wins. You do not need to win all 3."
- This is NOT an elimination bracket. The panel must not render a bracket or knockout structure.
- The "no Shop Coins" warning is visible.
- The Hammer-saving note is a sourced strategy reminder, not a recommendation. It states the economic fact.

### FC-TB-9: Unique Skill Gate Display (Trackblazer variant)

```
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
```

**Rendering rules:**

- Shows BOTH fans and bond as co-requirements.
- Neither is sufficient alone. The gate is not met until both are reached.
- This differs from URA/Unity Cup (fans only) and must not reuse their single-requirement layout.
- The bond values (19, 31, 51) are sourced from uma.guide. They are displayed as the Trainer enters them.

---

# PART 3: SHARED RULES FOR BOTH SCENARIOS

## 3.1 What the Tool Never Does

These rules apply to both Unity Cup and Trackblazer, and to every scenario:

1. **No simulation.** The tool does not predict race outcomes, training results, or stat trajectories. (Planner Rule 1, PRD §6.11)
2. **No recommendation.** The tool does not suggest which training to pick, which race to enter, or which item to buy. (Planner Rule 5)
3. **No unexplained numbers.** Every figure on screen is either Trainer-entered or a sourced constant with a visible citation. (Planner Rules 4, 5)
4. **No scenario chrome from another scenario.** Unity Cup elements do not appear in Trackblazer. Trackblazer elements do not appear in Unity Cup. Absent beats empty. (D-220, D-221)
5. **No secret-event tracking in Trackblazer.** Secret events do not fire in Trackblazer. The tool must not show a secret-event tracker or imply one exists. (D-221)
6. **No Race Calendar in Trackblazer.** The Race Calendar panel is absent. The Grade Point meter replaces it. (D-221)
7. **No Scenario Link in Trackblazer.** The Scenario Link identity element is absent from the header. (D-216)

## 3.2 Scenario Composition Rule

The dashboard is one spine. The only thing that changes per scenario is which panels the goal region composes (D-220, D-240).

| Panel                  | URA Finale  | Unity Cup                   | Trackblazer                  |
| ---------------------- | ----------- | --------------------------- | ---------------------------- |
| Turn chip              | Present     | Present                     | Present                      |
| Energy gauge           | Present     | Present                     | Present                      |
| Fan readout            | Present     | Present                     | Present                      |
| Stat band              | Present     | Present                     | Present                      |
| Timeline               | Present     | Present                     | Present                      |
| Race Calendar          | Present     | Present                     | **ABSENT**                   |
| Grade Point meter      | Absent      | Absent                      | **Present**                  |
| Team Rank gauge        | Absent      | **Present**                 | Absent                       |
| Spirit Burst roster    | Absent      | **Present**                 | Absent                       |
| Team Race schedule     | Absent      | **Present**                 | Absent                       |
| Shop Coin counter      | Absent      | Absent                      | **Present**                  |
| Epithet tracker        | Absent      | Absent                      | **Present**                  |
| Race Fatigue chip      | Absent      | Absent                      | **Present** (when count ≥ 1) |
| Scenario Link identity | Aoi Kiryuin | 5 characters, via Team Name | **ABSENT**                   |
| Facility level cause   | Repetition  | Team Rank                   | Repetition + Purchased       |

An undescribed scenario (e.g., Our Grand Concert) renders the baseline strip plus its known caps, and nothing else (D-241).

## 3.3 Facility Level Source Labels

Every facility level chip must state its cause. The cause differs per scenario:

| Scenario    | Level chip text                             |
| ----------- | ------------------------------------------- |
| URA Finale  | `Speed Lvl 3 ← 3× repetition`               |
| Unity Cup   | `Speed Lvl 4 ← Team Rank A`                 |
| Trackblazer | `Speed Lvl 5 ← 4× repetition + 1 purchased` |

A level chip without its cause is a review failure (D-222).

## 3.4 Summer Camp (Both Scenarios)

Summer Camp is identical across all scenarios:

- Two windows: Classic Year and Senior Year.
- Each window: 4 turns (Early July to Late August).
- All facilities set to Lv5 simultaneously.
- Rest and Recreation restore both Energy and Mood.
- The camp ends with +5 to 3 random stats.

The tool marks the camp window on the timeline and shows the Lv5 override on all facility tabs. The facility level source label during camp reads: `Lvl 5 ← Summer Camp`.

## 3.5 Timeline Entry Differences

Timeline entries carry scenario-specific fields:

| Field             | URA Finale          | Unity Cup                                       | Trackblazer                                        |
| ----------------- | ------------------- | ----------------------------------------------- | -------------------------------------------------- |
| Training gains    | Standard            | Standard + Special Training bonus + Burst gains | Standard                                           |
| Teammate stats    | N/A                 | Per-teammate stat gains, Spirit gauge change    | N/A                                                |
| Skill hint source | Event / card pool   | Burst: card's own hint pool (non-random)        | Event / card pool                                  |
| Race result       | Placement, fans, SP | Placement, fans, SP, Team Race context          | Placement, Grade Points, Shop Coins                |
| Scenario-specific | Goal completion     | Team Race result, league rank, Burst count      | Epithet progress, Rival Race result, fatigue count |

---

# PART 4: COMPONENT STATE SUMMARY

## 4.1 Unity Cup Component States

| Component          | States                                                                                                  |
| ------------------ | ------------------------------------------------------------------------------------------------------- |
| Facility tab       | normal, 1-flame, ≥2-flame, burst-ready, extreme-ready, summer-camp-Lv5                                  |
| Teammate card      | chargeable, charged, held, normal-spent, extreme-chargeable, extreme-spent                              |
| Team Race panel    | countdown, composition, opponent-select, racing, result, retry-available                                |
| Spirit Burst meter | filling (0–3), threshold-reached (4–6, 7–9, 10–12, 13+)                                                 |
| Team Name picker   | locked (before event), selectable, confirmed                                                            |
| Unique Skill gate  | not-reached, reached (fan-only for Feb/Dec), reached (fan+bond for April in URA; fan-only in Unity Cup) |

## 4.2 Trackblazer Component States

| Component           | States                                                                           |
| ------------------- | -------------------------------------------------------------------------------- |
| Grade Point meter   | objective-1 (debut), objective-2, objective-3, objective-4, completed            |
| Shop panel          | locked (before debut), open, restocking (countdown), sale-active, limited-active |
| Shop item           | available, held (N/5), active (multi-turn), overwritten, expired                 |
| Epithet route       | hidden (0 races), active (1+), completed, reward-claimed                         |
| Rival Race badge    | hidden, available, won, drawn, lost                                              |
| Race Fatigue chip   | hidden (count 0), visible (count 1+), gone (after Late Dec)                      |
| Climax panel        | locked, race-1, race-2, race-3, completed                                        |
| Facility level chip | repetition-only, repetition+purchased, summer-camp-Lv5                           |

---

# PART 5: OPEN ITEMS

Items that remain unresolved and must NOT be filled by inference:

| Item                                            | Status                                                                                                                                                                                               |
| ----------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Our Grand Concert mechanics                     | ❌ Not extracted. Baseline + caps only. (D-241)                                                                                                                                                       |
| Unity Cup facility activity names per facility  | ❌ Not captured for Unity Cup specifically.                                                                                                                                                           |
| Trackblazer Debut Race Energy cost              | Sourced as "costs Energy" but exact value not stated.                                                                                                                                                |
| Radiant Star skill details                      | Name appears in guide; mechanics not extracted.                                                                                                                                                      |
| Trackblazer aptitude exception thresholds       | 30/200/300 and 60/200/300 sourced from uma.guide. Exact aptitude criteria for "high-dirt/low-turf" and "narrow-range turf" not formally defined.                                                     |
| Extreme Spirit Burst bond-gauge bypass          | Whether the ≥80 bond requirement is also bypassed (in addition to the facility-type bypass) is NOT stated by any source. Carry as ❌.                                                                 |
| Trackblazer GP thresholds for the 4th objective | All tracks converge at +300. Confirmed.                                                                                                                                                              |
| Unity Cup pre-rework values                     | Superseded by the 2026-07-01 rework. Do not use pre-rework numbers. `docs/scenarios/02-unity-cup.md` is superseded by `06-unity-cup-gametora.md` wherever they disagree. |
