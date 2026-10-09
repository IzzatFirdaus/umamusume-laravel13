# URA Finale Plan

This document defines the URA Finale scenario for Trainer Desk. It is written in the same architectural style as the Unity Cup plan: it assumes the shared cockpit frame, the three-layer model (Trainer Home, Career Cockpit, Advisor), decision cards, visible uncertainty, and the Veteran loop, all of which carry over unchanged. Only the URA Finale-specific deltas are spelled out here.

## Sources

- `docs/scenarios/01-ura-finale.md` — 142-line scenario guide (fan gates, camp, facilities, stamina thresholds, deck shapes, mood effects)
- `docs/UMAMUSUME_REFERENCE.md` §2.2.1 — URA Finale `[Global]` mechanics summary (Happy Meek, fan gates, beginner targets)
- `config/scenarios.php:111-130` — config matrix entry (`cap_bonus`, `widgets`, `panels`, `facility_level_source`, `scenario_links`)
- `.scratch-uma/scenarios.json` — GameTora data export (`stats:[200,200,200,200,200]`, `start_en:1750897800`)
- `SCENARIO-PUBLISHER-REFERENCES.md:892-996` — Game8 extraction (July 1, 2026 rework confirmation)
- [Game8, URA Finale Scenario Guide](https://game8.co/games/Umamusume-Pretty-Derby/archives/536520) (last updated July 6, 2026)

**Cross-check note.** URA Finale enters SCENARIO-PUBLISHER-REFERENCES.md:33 "solely through the Game8 extraction" — uma.guide and GameTora cover only Trackblazer and Unity Cup. There is no second publisher to cross-check against for URA Finale. The config matrix and the scenario guide are the first author and the Game8 page the second; they agree on every figure cited below.

The July 1, 2026 rework added Happy Meek duels and raised caps from 1200 to 1400 in all stats. `config/scenarios.php:114` (`cap_bonus` = 200 each) and `.scratch-uma/scenarios.json` (`stats:[200,200,200,200,200]`) both record this; the Game8 page confirms it in §"Increased Stat Caps".

---

## 1. THE CENTRAL UX CONCEPT

URA Finale is the fundamentals scenario. There is no team, no shop, no mid-scenario currency, and no special program. The single thing layered on top of the base training loop is the **Happy Meek duel**:

```
         STAT TRAINING turn
               │
               ├─► Happy Meek may appear
               │        │
               │        ├─► WIN → stat gain + cap bump + racing-spirit hint + 30 SP
               │        └─► LOSE → minor stat gain + 15 SP
               │
               └─► 6 wins escalate her to powered-up state at the finals
                     WIN → Past My Limits skill + 200+ SP + enhanced spirit spark
```

The persistent question for URA is:

> Which training turn raises the stats I need for the next fan gate or goal race, and should I spend energy on a Happy Meek duel now or save it for a Summer facility upgrade?

---

## 2. SCENARIO SELECT

```
URA FINALE
Fundamentals. Happy Meek duels for cap increases.

Stat caps   SPD 1400  STA 1400  POW 1400  GUT 1400  WIT 1400
Facility level rises by repetition (every 4 same-stat trainings)
Scenario Link: Aoi Kiryuin
```

The +200 cap_bonus is from `config/scenarios.php:114` and `.scratch-uma/scenarios.json:stats`. Aoi Kiryuin is the scenario link; `config/scenarios.php:126` lists her and `docs/scenarios/01-ura-finale.md:16-19` confirms her event role.

---

## 3. TRAINEE & TARGET (DELTAS)

The career goals, fan gates, and goal-race ladder are the URA defaults carried from `docs/scenarios/01-ura-finale.md:77-84` and confirmed in Game8 §"Run More Races to Get Fans and Upgrade Unique Skill".

- **Fan gates** (Turf / Dirt): Valentine's Day 60,000 / 40,000 (Senior, early Feb); Fan Fest 70,000 / 60,000 (Senior, early Apr, with 3-bar Director Akikawa friendship); Holiday Season 120,000 / 80,000 (Senior, late Dec). Source: `docs/scenarios/01-ura-finale.md:80-83`, Game8 §"Run More Races...", UMAMUSUME_REFERENCE.md §2.2.1.
- **Stamina floors** by distance: Sprint 350–450, Mile 450–550, Medium 600–700, Long 700–800. Source: `docs/scenarios/01-ura-finale.md:66-73`, UMAMUSUME_REFERENCE.md §2.2.1.
- **Unique Skill level-up gates** at those same fan thresholds. Source: same as fan gates above.
- **Goal race distance.** The finale distance is determined by the most-raced distance type across the career; ties resolve to the shortest distance. This is the URA-specific distance ledger and the one surface where URA diverges from the other scenarios' fixed calendars. Source: `docs/scenarios/01-ura-finale.md:140` ("plan a race calendar"), confirmed by Game8 §"Ensure You Have Enough Stamina" listing all four distance-specific thresholds as the decision driver.

No new goal-race or fan-gate screen is needed — the shared `race_calendar` panel handles distance tracking and the `career_goals` panel handles the gates. Both are `true` in `config/scenarios.php:117-124`.

---

## 4. LEGACY LAB (DELTAS)

Three scripted Inspiration events scale stat boosts off the Legacy's own build — Junior Year start, Classic Year early April, Senior Year early April. Source: `docs/scenarios/01-ura-finale.md:43`.

There is **no scenario factor** in URA Finale. Unlike Unity Cup (`Power` and `Wit` on inheritance) or Trackblazer (distance-dependent stat grants), URA's Legacy inherits base stats and skills only. The Legacy Lab screen should show no scenario-factor badge to avoid implying a bonus that does not exist.

---

## 5. SUPPORT DECK BUILDER (DELTAS)

Deck templates from `docs/scenarios/01-ura-finale.md:56-80` and confirmed in Game8 §"Use Proper Support Cards That Will Benefit Your Umamusume":

| Distance   | Build 1                | Build 2                          |
| ---------- | ---------------------- | -------------------------------- |
| Sprint     | 4× Speed, 2× Power     | 3× Speed, 1× Wit, 2× Power       |
| Mile       | 4× Speed, 2× Power     | 3× Speed, 1× Wit, 2× Power       |
| Medium     | 4× Speed, 1× Friend/Guts, 1× Power | 3× Speed, 1× Wit, 1× Friend/Guts, 1× Power |
| Long       | 4× Speed, 2× Friend/Guts | 3× Speed, 1× Wit, 2× Friend/Guts  |

Key principles:
- Check the skills a card provides — a card's built-in skill matching the trainee's running style/distance becomes purchasable during training. Source: Game8 §"Pay Attention to Skills that Cards Provide".
- Speed is close to universally valuable regardless of build.
- Stamina cards are best avoided (you get Stamina mainly from stats, and the Stamina floor is a hard prerequisite, not a flex).

Present these as community guidance only; the Trainer's own cards and distance goal override them.

---

## 6. RUN PREFLIGHT

```
RUN PREFLIGHT                                    URA Finale

DECK          Race Bonus 41%   ✓ adequate for beginner targets
              Aoi Kiryuin (SR) ✓ Scenario Link present
LEGACY        [Legacy Uma Name]  → Inspiration events scale on this
TARGET        Sprint  →  Speed 800+, Stamina ≥ 400
              Mile    →  Speed 800+, Stamina ≥ 500
              Medium  →  Speed 800+, Stamina ≥ 600
              Long    →  Speed 800+, Stamina ≥ 700
GOALS         URA fan gates   ✓ planned
FANS          Valentine 60k   ✓ (10 weeks out)
              Fan Fest 70k    ✓ (22 weeks out, Akikawa friendship planned)
              Holiday 120k    ✓ (final gate)
CAMP          Summer Y2: Jul–Aug, 4 turns, all Lvl 5  ✓ timed
              Summer Y3: Jul–Aug, 4 turns, all Lvl 5  ✓ timed
DUels         Happy Meek     0/6 duels won so far
              Past My Limits  ✗ not yet in reach

RULE CONFIDENCE
  Stat caps                 config/scenarios.php:114 + .scratch-uma/scenarios.json
  Fan gates                 docs/scenarios/01-ura-finale.md + Game8
  Stamina floors            docs/scenarios/01-ura-finale.md + UMAMUSUME_REFERENCE.md §2.2.1
  Training level (repetition) config/scenarios.php:127 ($facility_level_source = 'repetition')
```

---

## 7. THE CAREER COCKPIT

```
┌──────────────────────────────────────────────────────────────────────┐
│ URA FINALE                 CLASSIC • TURN 31 • Energy 58           │
├──────────────────────────────────────────────────────────────────────╢
│ FACILITY LEVELS              Training facility level by repetition  │
│ Speed       3    (12 trainings)                                     │
│ Stamina     2    (8 trainings)                                      │
│ Power       3    (12 trainings)                                     │
│ Guts        2    (8 trainings)                                      │
│ Wit         3    (12 trainings)                                     │
├──────────────────────────────────────────────────────────────────────╢
│ FAN GATES                                                           │
│ Valentine's Day    48,000 / 60,000 (Turf) — 14 turns to gate        │
│ Fan Fest           32,000 / 70,000 (Turf) — 22 turns to gate        │
│ Holiday Season     18,000 / 120,000 (Turf) — 44 turns to gate       │
├──────────────────────────────────────────────────────────────────────╢
│ DUEL TRACKER                                                        │
│ Happy Meek:  3 / 6 wins   (2 more to escalation)                    │
│ Racing Spirit hints earned: Speed Lv1, Power Lv1                    │
├──────────────────────────────────────────────────────────────────────╢
│ ★ RECOMMENDED: TRAIN STA                                            │
│                                                                  │
│ 4 flames: +6 Sta, +3 Guts, +5 SP (repetition Lv3)                  │
│                                                                    │
│ WHY                                                                  │
│ Stamina is your weakest floor for a Medium-distance build.           │
│ Facility is at Lv 2; one more block of 4 reaches Lv 3.               │
│ Energy 58 → 18 after training. Summer starts in 6 turns;              │
│ you need 60 for 4 Lvl-5 facility turns. Recreation on turn 33        │
│ covers the gap.                                                    │
│                                                                    │
│ [ TRAIN STA ]                                                      │
│ Alternatives                                                       │
│ ○ Speed: +facility, but Stamina floor is the blocker                 │
│ ○ Optional race: only if a goal requires it                          │
└──────────────────────────────────────────────────────────────────────┘
```

All numbers in that wireframe are illustrative except the rule they encode: facility level rises every 4 repetitions (`config/scenarios.php:127`), and Stamina is a floor not a flex stat.

---

## 8. HAPPY MEEK DUEL PANEL (NEW, THE KEY IN-RUN SCREEN)

This is the URA-only mechanic, so it gets a dedicated panel — not folded into the generic cockpit.

### When she appears

Happy Meek appears on any stat-training turn after the "I'm Here to Challenge You" event. Source: Game8 §"Training With Happy Meek". She does **not** give Friendship trainings (unlike Team Sirius or Heirs to the Throne). Source: Game8 §"Training With Happy Meek".

### Duel choice

You are prompted to pick between three options: two random stats and the stat you dueled Happy Meek on. Each option has an icon indicating win chance. Source: Game8 §"Duel for Additional Stat Cap Increases and Skill Points".

### Rewards

| Outcome | Primary                     | Secondary        | Cap bump | Skill Points | Racing Spirit hint |
| ------- | --------------------------- | ---------------- | -------- | ------------ | ------------------- |
| Win     | Stat gain (varies)          | Related stat +SP | +1 tier  | 30           | Yes (stat-appropriate) |
| Lose    | Minor stat gain             | —                | —        | 15           | —                   |

Source: Game8 §"Duel for...", UMAMUSUME_REFERENCE.md §2.2.1.

### Escalation

After **6 wins**, Happy Meek escalates to a powered-up state at the finals. Beating her there grants:
- The `Past My Limits` skill (source: Game8 §"Powered Up Happy Meek", and the linked skill page title)
- 200 or more Skill Points
- A chance at an enhanced Racing Spirit spark (carries a stat bonus and a skill hint into inheritance)

Source: Game8 §"Powered Up Happy Meek", UMAMUSUME_REFERENCE.md §2.2.1.

### Racing Spirit skills

Each Racing Spirit skill activates on the stat you dueled Happy Meek on. Source: Game8 §"New Racing Spirit Skills":

| Skill                    | Effect                                                  |
| ------------------------ | ------------------------------------------------------- |
| Racing Spirit: Speed     | Slightly increase velocity upon approaching late-race   |
| Racing Spirit: Stamina   | Moderately expend endurance to moderately increase velocity in the last spurt |
| Racing Spirit: Power     | Slightly increase velocity for a medium duration when passing another runner in the last spurt |
| Racing Spirit: Guts      | Slightly increase acceleration when dueling             |
| Racing Spirit: Wit       | Slightly increase velocity upon activating 2 skills during the second half |
| Racing Spirit: Mood      | Stamina, Guts, and Wit might moderately increase when mood is good or great |

Display these as hints earned — the duel tells you which Racing Spirit skill the hint is for, and winning a powered-up duel enhances the spark that carries the hint into inheritance.

---

## 9. ADVISOR PRIORITY HIERARCHY

1. **Don't end the run.** The URA goal races, fan gates, and energy come first, and the Alarm Clock covers goal-race failures.
2. **Summer timing.** Is Summer within 6 turns? Bank full energy and good mood now — a forced Rest during Summer wastes the free Level-5 facility turns.
3. **Fan gate runway.** How many turns until the next fan gate, and is your race calendar on track to clear it?
4. **Happy Meek duel economics.** Cash the duel now (energy cost vs. stat gain + cap bump + SP) or save energy for Summer. A duel win also raises a cap, which matters for Long builds hitting the 1200→1400 transition.
5. **Facility level.** Track repetition counts; a stat at 8/12 trainings is one block away from the next level.
6. **Stamina floor.** Before a Medium or Long race, is Stamina at the floor? If not, that training turn is non-negotiable.
7. **Training target.**
8. **Optimization.**

---

## 10. CAREER REPORT

URA Finale specifics for the report: final stat breakdown, which fan gates were cleared, Summer camp utilization, Happy Meek duel record (wins / escalation), Racing Spirit hints earned, unique skill level at career end, and whether the finale was won. Then offer "Promote to Veteran" as before.

Post-race rewards (Game8 §"After URA Finale Races"):
- **Qualifier win:** All stats +10, +40 Skill Points, affected by Race Bonus.
- **Semifinals win:** All stats +10, +60 Skill Points, affected by Race Bonus.
- **Finals win:** All stats +10, +80 Skill Points, affected by Race Bonus.

---

## 11. WHAT I COULD NOT CONFIRM

1. **Per-option duel win-chance percentages.** Game8 states each option "has an icon which indicates your chances of winning" but does not print the percentage values. Read the icon from the game; the plan's duel rewards table is confirmed, the exact probability per option is not.

2. **Happy Meek stat-gain formulas.** Game8 says winning grants "stat gains" and a cap bump but does not quantify the per-stat gain values beyond "the stat you dueled on." The 30/15 Skill Point split and the Racing Spirit hint award are confirmed; the exact stat-delta per win is not in any cited source.

3. **Aoi Kiryuin event bonuses.** `docs/scenarios/01-ura-finale.md:17` states she "boosts your stats via her interactions with another character, Happy Meek" but does not quantify the multiplier. Game8 §"Aoi Kiryuin is the Scenario Link" confirms the link role and recommends the SR card to amplify effects, but gives no numeric bonus. Treat this as a qualitative bonus, not a quantified one.

4. **Turns-per-year count.** The Game8 calendar uses relative timing ("After 11 turns", "Early July", "Late August") but does not give a total turn count per year. The Summer window is confirmed as 4 turns, Early July through Late August, in both Classic and Senior years. The exact turn offset from career start to each event is in `docs/scenarios/01-ura-finale.md:86-132` but the source notes (line 88-92) explicitly defer race-timing to `docs/scenarios/09-global-race-calendar.md`.

5. **Powered-up Happy Meek exact stat values.** Game8 §"Powered Up Happy Meek" states she "gets increased stats and is overall harder to beat" but does not list the specific numbers. The reward list (Past My Limits skill, 200+ SP, enhanced spark) is confirmed.

6. **Enhanced Racing Spirit spark mechanics.** "A chance to get an enhanced spark" — the exact trigger rate and the exact stat-bonus amount are not quantified in any cited source.

7. **Raffle Time! outcomes.** Game8 §"Raffle Time!" lists one of four random effects but does not state the probability distribution among them. Treated as a non-quantified random event.

8. **New Year's Resolutions / New Year's Shrine Visit choice weights.** Game8 lists the three options per event but does not state whether the choice is purely player-selected (it is — these are choice events, not random). This is confirmed by the scenario guide calling them "Choice:" events (`docs/scenarios/01-ura-finale.md:117-119`); the plan records the options, not probabilities.

---

## WHAT I WOULD NOT BUILD

- An auto-player for Happy Meek duels (read the icon, let the Trainer decide).
- A fixed "best deck" that overrides the Trainer's collection (present deck templates as guidance, not prescription).
- The enhanced Racing Spirit spark rate as a numeric prediction (no source quotes it).
- A stamina floor that silently defaults to a value a run has not recorded (render `N/A` with a title, never a default).

---

## MVP SCREEN SET

1. Trainer Home
2. Trainee & Target
3. Legacy Lab
4. Support Deck Builder
5. Run Preflight
6. Career Cockpit (with Fan Gates, Facility Levels, and Duel Tracker)
7. Happy Meek Duel Panel (in-run overlay triggered on a duel appearance)
8. Career Report / Veteran

§6 and §7 get most of the URA-specific design effort. The Duel Panel exists because URA Finale is the only scenario where a single in-run choice (duel stat + option) carries a cap-bump reward, and that choice is opaque without the panel surfacing the Racing Spirit hint tied to each result.

---

## 12. DECISION CONTRACT

Every recommendation the Advisor produces follows this shape — it must not be a bare action label:

1. **Current state and immediate objective.**
2. **Recommended action** and expected benefit (with numbers).
3. **Best alternative** and its trade-off.
4. **Confidence** — verified, derived, configurable, or unknown — with the source badge.
5. **Conditions that would change the recommendation.**

Example:

> **★ RECOMMENDED: TRAIN STA**
>
> You are at 48,000 / 60,000 fans for Valentine's gate, 14 turns out, and Stamina is below the Medium-distance floor (600–700).
>
> - **Train Stamina (Lv 2):** +8 Sta, +4 SP. Facility is 6/8 to Lv 3.
> - **Alternative — race a Tier 2 (Mile):** +4 Skill Points, +fans, but consumes energy and risks mood drop.
> - **Confidence: verified** (facility level by repetition, config `facility_level_source = 'repetition'`).
> - **Switch if:** Summer camp starts in 5 turns (bank energy first), or Happy Meek appears on a Speed tile (cap-bump opportunity).

---

## 13. CAREER MILESTONE TRACKER

URA Finale is a deadline scenario: three goal races with win-or-end stakes, three fan-gate events, and two Summer camps. The cockpit should surface a timeline, not just the current turn.

```
CAREER MILESTONES

UPCOMING
  • Valentine's Day gate    60,000 fans    14 turns    ⚠ 12,000 short
  • Summer camp (Y2)        4 turns        18 turns    Energy: 58 / 60
  • Fan Fest gate           70,000 fans    22 turns    ⚠ 32,000 short
  • URA Qualifier           Win required   36 turns    Speed target: 800+

COMPLETED
  • Junior debut            ✓ (Turn 11)
  • Inspiration 1           ✓ (Career start)
  • Inspiration 2           ✓ (Classic Apr)
  • 3-bar Akikawa friend    ✓ (for Fan Fest)
```

The timeline should distinguish **optimal now** (e.g., train Stamina) from **keeping the run viable later** (e.g., bank energy before Summer, hit Valentine's gate for Unique Skill upgrade).

---

## 14. ACCEPTANCE CRITERIA

| Feature                    | Success Criterion                                                                 | Edge Case                              |
| -------------------------- | --------------------------------------------------------------------------------- | ---------------------------------------- |
| Career Cockpit             | Shows stat caps, facility levels, fan gates, and duel count in a single view      | No fans yet (N/A, not 0)                 |
| Happy Meek Duel Panel      | Surfaces the 3 options, their icons, and Racing Spirit hint tied to each outcome  | All 6 duel wins achieved (escalation shown) |
| Career Milestone Tracker   | Surfaces each gate 5+ turns in advance, flags shortfalls                          | Gate already missed (run-at-risk flag)   |
| Stamina floor check        | Flags before any Medium/Long race if Stamina is below the distance floor          | Dirt surface (different floor threshold) |
| Recommendation reconciliation | Predicted outcome vs actual entry updates the run state and flags deviation   | User edits state manually              |

---

## 15. VERIFICATION STATUS

Each feature carries one of three levels — never collapsed:

- **Implemented** — the code exists and the behavior is connected.
- **Verified** — automated tests or reproducible manual checks demonstrate the intended behavior.
- **Game-validated** — calculations have been compared against authoritative mechanics or observed in-game.

A page that renders is not verified. A verified calculation can still rest on an incorrect understanding of the game. This distinction is tracked per feature.

---

## 16. IMPLEMENTATION AND TESTING PLAN

| Phase                      | Deliverable                                                                     | Completion evidence                                              |
| -------------------------- | ------------------------------------------------------------------------------- | ---------------------------------------------------------------- |
| 0. Reconnaissance          | Inspect existing scenario code, plans, contracts, routes, data models and tests | Inventory of existing, partial, missing and conflicting behavior |
| 1. Rules audit             | Document verified scenario mechanics and unresolved assumptions                 | Source-backed rules and explicit unknowns                        |
| 2. State model             | Define run state, transitions, persistence and validation                       | Tests for valid and invalid state changes                        |
| 3. Decision engine         | Implement scenario-specific candidate evaluation and ranking                    | Deterministic tests using known inputs and expected outputs      |
| 4. Command center          | Build the primary screen around the next actionable decision                    | UI tests for loading, empty, error, stale and normal states      |
| 5. Reconciliation          | Compare predicted versus actual outcomes                                        | Tests for updating run state without losing history              |
| 6. End-to-end verification | Exercise complete careers and edge cases                                        | Reproducible scenarios, regression results and completion report |

**URA Finale focus:** phase 3 builds the scenario rules layer (repetition-based facility levels, fan gates, Happy Meek duel rewards as verified). Phase 4 builds the Career Cockpit with the Duel Panel. Phase 5 adds reconciliation for duel outcomes (predicted stat gain vs. actual). Phase 6 validates cap-bump behavior against in-game captures.

---

## 17. PRIORITY MATRIX

| Priority | Scope                    | Rationale                                                  |
| -------- | ------------------------ | ---------------------------------------------------------- |
| P0       | Canonical run state, verified mechanics, regression tests | Protects the trustworthiness of the fundamentals scenario    |
| P1       | Next-action recommendations, deadline tracking, duel preview | Turns Cockpit from display into a command center          |
| P2       | Stat-cap ceiling arithmetic, distance-floor optimization | Materially affects finale eligibility                     |
| P3       | Keyboard-friendly controls, richer historical analytics | Convenience only                                           |
