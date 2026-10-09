# Trackblazer — Implementation Plan

**Project:** Trainer Desk 2.0  
**Scenario:** Trackblazer (Make a New Track / MANT)  
**Purpose:** Spec the Trackblazer career cockpit, the screens that differ from Grand Concert, and the pre-run planning flow.

---

## Sources

- `docs/UMAMUSUME_REFERENCE.md` (sections 1.3.4, 1.4.8, 2.2.3, 2.5, 2.7)
- [`GameTora, Trackblazer Scenario`](https://gametora.com/umamusume/trackblazer) (last updated 2026-03-12)
- [`uma.guide, Trackblazer Guide`](https://uma.guide/guides/trackblazer) (last updated Apr 29, 2026)
- [`Game8, Trackblazer Scenario Guide`](https://game8.co/games/Umamusume-Pretty-Derby/archives/580723) (returned pre-release copy)
- `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` sections "Trackblazer (uma.guide)" and "Trackblazer (GameTora)"

---

## 1. The Central UX Concept

Grand Concert is a conversion loop: training makes Performance, Lessons turn it into Songs, Songs raise Hype. Trackblazer is an economy loop with a hard deadline. The persistent question changes to:

**Race, train, or rest this turn, and does it keep me alive at the next Grade Point checkpoint?**

About half of all turns are races, and a typical career runs 30 to 40 of them. That is why the race decision, not the training decision, should lead the cockpit.

```
   RACE ──► placement ──► Grade Points + Shop Coins + stats/SP/fans
    ▲                          │                 │
    │                          ▼                 ▼
    │                   CHECKPOINT          SPECIAL SHOP
    │                  (60 / 300 / 300)          │
    │                   miss = run ends          ▼
    │                                    ITEMS (energy, training
    │                                    boosts, race bonus, ...)
    │                                            │
    └──────── stronger races / training ◄────────┘

   EPITHMET ROUTES: win every race in a route = bonus stats/hints
   FINALE: Twinkle Star Climax (3 races, Victory Points)
```

Three meters stay on screen all run: Grade Point runway, Coin bank, and Race fatigue. They play the role Performance played in the Grand Concert cockpit.

---

## 2. Scenario Select

The scenario card should show what makes Trackblazer different. It also loads the rules engine.

```
TRACKBLAZER
Race-driven career. Grade Points replace career goals.

Stat caps   SPD 1200  STA 1900  POW 1200  GUT 1200  WIT 1500
No career goals  •  No secret events  •  No Scenario Link
```

The caps are the figures in the reference, and whether they are live on Global today is still open (section 15). The engine loads these tables, each with a source badge:

- Grade Point objectives, by checkpoint profile
- Grade Points per race grade, and the placement modifiers
- Shop Coins by placement
- The shop catalog
- Race-fatigue odds
- Epithet routes
- Year-end unique-skill gates
- Finale rules

---

## 3. Trainee & Target

Trackblazer needs four checks that the Grand Concert flow did not.

**Checkpoint profile.** Both sources agree on three of the numbers, so default to the standard profile and let the Trainer override it:

- Standard: 60 / 300 / 300.
- High-dirt, low-turf trainees (Haru Urara is the example both sources give): 30 / 200 / 300.
- Short-distance turf trainees (Curren Chan is GameTora's example): sources disagree on the first number (section 15).

Show the profile as editable, with a source badge, and tell the Trainer to confirm it against the in-game objectives screen.

`config/scenarios.php` carries all three tracks (`standard` / `dirt_leaning` / `limited_turf_range`); `TrainingRun::gradeObjectives()` renders only `standard` and says so, because a wrong denominator is worse than a conservative one. Tracked as **KI-15**, OPEN.

**Race coverage.** Mile and Medium dominate graded racing. By uma.guide's counts, 24 of 30 unique G1s, 25 of 36 G2s and 50 of 69 G3s are Mile or Medium. The guide says to bring at least a C, ideally a B, in both. So show a coverage number:

```
RACE COVERAGE
Graded races this trainee can run at C or better:
G1  21 / 30    G2  22 / 36    G3  44 / 69
⚠ Weak Medium aptitude: 14 G1s are Medium
```

The values above are illustrative. The real numbers come from the aptitude data joined to the race list.

**Secret-event warnings.** Secret events and career goals are disabled. Both guides give the same example: Silence Suzuka cannot get Runaway here. If the Trainer's target relies on a secret event, flag it before the run starts.

`docs/UMAMUSUME_REFERENCE.md` §2.2.3 confirms: "Character secret events do not fire here, which is the mechanical reason a character cannot unlock an extra strategy option in this scenario."

**Cap-aware targets.** Speed, Power and Guts cap at 1200, Stamina at 1900, Wit at 1500. The target screen should show headroom per stat, so a Trainer does not aim Speed at 1400.

---

## 4. Legacy Lab (Deltas Only)

- No Scenario Link badge, because Trackblazer has zero linked characters. `docs/UMAMUSUME_REFERENCE.md` §2.2.3: "this scenario has zero linked characters, the only one of the fourteen with none."
- The scenario factor is **"Climax Scenario"**, which gives Stamina and Guts on inheritance. `docs/UMAMUSUME_REFERENCE.md` §2.7: "The Trackblazer scenario factor is printed as 'Climax Scenario' and grants Stamina and Guts bonuses on successful inheritance."
- Only show cap-raise projections where the data supports them. The cap-breakthrough rule in the reference is JP-tagged. The Global 2026-07-01 rework raises caps above the 1200 base; whether the raised Trackblazer caps (1900 Stamina, 1500 Wit) are live on Global today is open (section 15).

---

## 5. Support Deck Builder (Deltas)

Deck effects you evaluate:

- Race Bonus (effect id 15). uma.guide says to stay at or above 50. Draw a meter with a marker at 50, labelled as a community guideline and not a game rule.
- Fan Bonus (16).
- Skill Point Bonus (30).
- Failure Protection (27).
- Energy Cost Reduction (28).
- Bond coverage.

Archetypes are suggestions, not rules. The guide describes two frameworks:

- Speed + Wit: 2 Speed and 2 Wit, with the remaining slots flexed for skills or Stamina.
- Wit + Guts: 3 Guts and 2 Wit, leaning on Guts Ankle Weights. It can cap Guts, but skills and Stamina are harder to source.

Detect which shape the deck resembles, but evaluate the real cards.

**Friendship is valued differently here.** uma.guide says a lone friendship training often isn't worth skipping a graded race, because race rewards are high and Trackblazer training bases are low. Base facility values match Unity Cup's (lower than URA Finale's): Speed +8/+4/+2 SP, Stamina +7/+3/+2 SP, Power +4/+6/+2 SP, Guts +3/+3/+6/+2 SP, Wit +2/+6/+3 SP (energy costs −19 to −20, Wit +5). The Advisor should compare friendship value against race value instead of assuming friendship wins.

Source: `SCENARIO-PUBLISHER-REFERENCES.md` lines 260-268 and 1276-1280.

---

## 6. Route Planner (New, and the Key Pre-Run Screen)

This takes the place the Concert Planner held in the Grand Concert flow. The route is a plan, not a script, and both guides say to deviate when a training turn is exceptional.

```
ROUTE PLAN                             Rice Shower • Standard profile

JUNIOR         ──●debut───────────────────●60 GP checkpoint──
 GP this period   0 / 60   planned: 100   floor (2nd-3rd): 52

CLASSIC        ─────[SUMMER]──────────────●300 GP checkpoint─
 GP this period   0 / 300  planned: 340   floor: 214

SENIOR         ─────[SUMMER]──────────────●300 GP─[FINALE]───
 GP this period   0 / 300  planned: 320   floor: 190

EPITHMET ROUTES IN PLAN
 Oka Sho / Oaks / Shuka Sho line    1 of 3 planned   ⚠ 2 races unscheduled
 Spring Champion                    0 of 3
 Mile route                         2 of 3

FLAGS
 ⚠ 5 consecutive races planned in Classic spring (fatigue risk)
 ⚠ Year-end gate, Classic: 60,000 fans + Akikawa bond 31
 ○ Rival races are random; they cannot be scheduled
```

The numbers above are illustrative. What is sourced:

- Grade Points for a win: G1 100, G2 80, G3 60, OP 40, Pre-OP 20. Source: `SCENARIO-PUBLISHER-REFERENCES.md` lines 92-100.
- Placement modifiers (uma.guide, single source): 1st 100%, 2nd 60%, 3rd 40%, 4th-5th 20%, 6th and lower 10%. Source: `SCENARIO-PUBLISHER-REFERENCES.md` lines 102-110.
- Points never carry over. Each period starts at zero. Source: both publishers, confirmed in `docs/UMAMUSUME_REFERENCE.md` §2.2.3.

So the planner can show three numbers per period: needed, planned if you win, and a floor if you finish 2nd to 3rd. A G2 win is 80 and a G2 second place is 48.

---

## 7. Run Preflight

```
RUN PREFLIGHT

TRAINEE   Rice Shower     SCENARIO  Trackblazer
PROFILE   Standard 60 / 300 / 300 (confirm in game)

CHECKPOINTS
 ✓ Junior 60 GP reachable (plan 100, floor 52)
 ⚠ Floor is under target: needs 1+ win
 ✓ Classic / Senior reachable

RACE COVERAGE      ✓ Mile B  ✓ Medium A  ○ Long A
DECK               ✓ Race Bonus 56%   ⚠ Stamina coverage low
ROUTES             ✓ 3 epithet routes planned
YEAR-END GATES     ⚠ Akikawa bond plan needed (19 / 31 / 51)
SECRET EVENTS      ✓ Plan does not depend on any

RULE CONFIDENCE
 GP table, coin table, shop    community-sourced
 Checkpoint profile            confirm in game

[ START CAREER ]
```

---

## 8. The Career Cockpit

```
┌─────────────────────────────────────────────────────────────────────┐
│ TRACKBLAZER                          CLASSIC • TURN 31 • Energy 54  │
├─────────────────────────────────────────────────────────────────────┤
│ GRADE POINT RUNWAY            period needs 300                      │
│ ███████████░░░░░░░░░  172 / 300    128 short • 9 turns left        │
│ Races that fit: 2 G2 wins + 1 G3 win ≈ 220                          │
├─────────────────────────────────────────────────────────────────────┤
│ COINS  210       SHOP refresh in 3 turns      ITEMS 7 (cap 5/item) │
│ RACE FATIGUE  2 consecutive races  → next race: see table          │
├─────────────────────────────────────────────────────────────────────┤
│ ★ RECOMMENDED: RACE (G2, mile)                                      │
│                                                                     │
│ 1st  +80 GP  +100 coins   2nd  +48 GP  +60 coins   3rd  +32 GP  +60 │
│                                                                     │
│ WHY                                                                 │
│ You are 128 GP short with 9 turns left. A G2 win is worth 80.       │
│ Training Speed here gains less than the checkpoint risk costs.      │
│ ⚠ A third consecutive race raises fatigue risk (60% Mood Down).     │
│                                                                     │
│ [ RACE ]                                                            │
│                                                                     │
│ Alternatives                                                        │
│ ○ Train Wit: safer, +energy, does not move the runway               │
│ ○ Rest: clears the race chain, loses a turn of GP                   │
│ ○ Use Good-Luck Charm, then train Power: 0% failure for 1 turn      │
└─────────────────────────────────────────────────────────────────────┘
```

Every number in that wireframe is illustrative. The tie to sources:

- 80 GP and 100 coins are the G2 first-place values. Source: `SCENARIO-PUBLISHER-REFERENCES.md` lines 92-99 and 116-121.
- 48 GP is 80 × 60%. Source: line 107.
- The fatigue figure comes from the table in section 10.

Two design points:

- The Race Desk should show the same breakdown for any race the Trainer picks. I would not display an exact placement probability, and I would show the 1st, 2nd and 3rd branches instead.
- After a period's threshold is met, further Grade Points do not help the checkpoint. The Advisor should say so ("checkpoint met; this race is now worth coins, stats and epithet progress only"). Races still pay coins, stats and fans after that.

---

## 9. Advisor Priority Hierarchy

1. Don't end the run. This is Grade Point feasibility at the next checkpoint, plus the debut race (which costs energy here).
2. Don't wreck the run. This covers energy floors and race fatigue.
3. Route integrity. An epithet needs every race in its route won, so a skipped race can break a route.
4. Economy. This is the coin bank, the shop rotation, the Summer stockpile and the finale reserve.
5. Training target.
6. Optimization.

The explanation shape stays the same: recommendation, reason, alternatives. For example, "Speed gives the most raw stats, but a race is better now because you are 128 GP short."

---

## 10. Race Fatigue Panel

Consecutive races matter more here than in any other scenario. uma.guide publishes the odds of the fatigue event by consecutive-race count. Show the row for the current count:

```
RACE FATIGUE          chain so far: 2

                       1 race   2 races   3 races   4+ races
Mood Down (1+ energy)    0%       0%        60%       100%
Mood Down (0 energy)    15%      33%       90%+      100%
Skin Outbreak (1+)       0%       0%        15%        33%
Skin Outbreak (0)        4%       8%        25%        33%
3 random stats -10 (any)  0%       0%         0%        40%

Cannot occur after late December.
Guaranteed recovery events: Classic early Feb (+1 mood), early Mar (+20
energy), late Sep (+1 mood); Senior late Jun (+20 energy), late Oct (+1
mood), late Dec (+30 energy).
```

Source: `SCENARIO-PUBLISHER-REFERENCES.md` lines 229-242.

Items mitigate this, so the Advisor should factor in what the Trainer is holding. The guide says to race without fear if you hold the right items. Two caveats:

1. The guide does not say whether "3 races" counts the upcoming race or the ones already run. Show the exact row being read, and flag the convention as unverified.
2. "After late December" does not say which year.

---

## 11. Shop Drawer (In the Cockpit, Not a Separate Page)

The shop is used almost every turn, so it should be a drawer in the cockpit. Display the rules the game imposes:

- The shop is locked until the Debut race. (`SCENARIO-PUBLISHER-REFERENCES.md` line 374)
- It refreshes every 6 turns. (line 375)
- A maximum of 5 copies per item. (line 376)
- Race-triggered items last 3 turns.
- Sales are 10% to 20%. (line 379)
- Multi-turn items cannot be used while still active. (line 380)
- A lower-grade item used before a higher one is wasted, and the reverse is not possible. (line 380)

```
SHOP                                coins 210   refresh in 3 turns

★ ADVISOR      Keep 100+ coins: Summer starts in 6 turns and the shop resets then

Master Cleat Hammer    40   +35% race bonus, 1 turn     held 1/5
Empowering Megaphone   70   +60% training, 2 turns      held 2/5
Reset Whistle          20   shuffle support cards       held 0/5
Good-Luck Charm        40   0% failure, 1 turn          held 1/5
Vita 65                75   +65 energy                  held 0/5

Community tags (uma.guide, opinion): Must Buy / Skip
```

Keep community opinions visibly separate from game rules. Items you will want to track:

- Training Applications (150 coins) permanently raise a facility level for the run. (line 433)
- Grilled Carrots (40) give +5 bond to all supports. (line 411)
- Yummy Cat Food (10) gives +5 Akikawa bond. uma.guide says to skip it (line 317), but section 14 gives a case where it matters.
- Scholar's Hat (280 coins) — "the equivalent of 3 first-place wins" — grants the Fast Learner discount on skill hints. (line 443)
- Stat books at +7 or +15, avoid the +3 ones — recommended purchase tiers. (line 444)
- Royal Kale Juice restores 100 Energy at the cost of 1 Mood; the mood debuff is fixed with a Cupcake. (line 445)
- Cleat Hammers do not stack: Master Cleat Hammer used after Artisan Cleat Hammer gives only the 35% from the Master, not a combined 55%. (line 447)
- Empowering Megaphone (60% training boost, 2 turns) overwrites an earlier Coaching Megaphone. (line 446)

Reserve rules from uma.guide, shown as guidance:

- Keep at least 100 coins entering Summer. (line 271)
- Stock two Megaphones for the four Summer turns. (line 275)
- Save about 150 coins for the final shop. (line 297)
- Save three Gold Hammers for the finale. (line 296)

Full item list source: `SCENARIO-PUBLISHER-REFERENCES.md` lines 383-453; Summer Camp window: line 1225; cleat hammer stacking and Empowering Megaphone overwrite: lines 446-447.

---

## 12. Epithet Tracker

Epithets pay random stats, and some give hints. Examples from uma.guide:

- Lady: win the Oka Sho, Japanese Oaks and Shuka Sho, for 2 random stats +10.
- Stunning: the Satsuki Sho, Japanese Derby and Kikuka Sho line, for +10.
- Goddess: Lady plus three more races, for +15.
- The dirt epithets: win 5, 10 or 15 dirt races, and 3, 4, 5 or 9 dirt G1s.

Source: `SCENARIO-PUBLISHER-REFERENCES.md` lines 125-176 (epithet tables).

Show each route as a chain: won, remaining, and whether it is still alive. If a race in a route is lost, grey the whole route out and say so.

**Summer Camp.** Early July to Late August, in both Classic and Senior Years, training facilities reach temporary Level 5. This is the highest-value training window for the scenario, and cleat hammers / megaphones reserved for it pay the most. Source: `SCENARIO-PUBLISHER-REFERENCES.md` line 1225 (`[Both]`).

---

## 13. Year-End Gate (Unique Skill Level-Up)

The gate is separate from Grade Points. In late December of each year the game picks the "Umamusume of the Year". uma.guide gives numeric thresholds:

- Junior: 5,000 fans and Akikawa bond 19.
- Classic: 60,000 fans and bond 31.
- Senior: 120,000 fans and bond 51.

GameTora describes the same gate as bar colors (blue 1 bar, blue 2 bars, green 3 bars) and says the fan and win requirements are unclear. The two are consistent if one bar is about 20 points. Treat the uma.guide numbers as single-source.

```
YEAR-END GATE  Classic, late December
Fans         41,200 / 60,000     ⚠ 18,800 short
Akikawa bond     24 / 31         ⚠ 7 short
```

Source: `SCENARIO-PUBLISHER-REFERENCES.md` lines 300-308.

If bond is the shortfall, the Advisor can note that Yummy Cat Food costs 10 coins for +5. That is a derivation from the item list (line 410). uma.guide itself says to skip the item (line 317), so label the recommendation as derived, not sourced.

---

## 14. Twinkle Star Climax Briefing

The finale is three races and three training turns. It is a mini-league, not an elimination bracket. Victory Points by placement:

- 1st 10, 2nd 8, 3rd 6, 4th 4, 5th-6th 3, 7th-9th 2, 10th-13th 1, 14th and lower 0.
- The maximum is 30, and the top total wins.

```
TWINKLE STAR CLIMAX PREFLIGHT

FINAL STATS      SPD 1180  STA 1620  POW 1090  GUT 880  WIT 1210
VICTORY POINTS   0 / 30 possible
TRAINING TURNS   3  (hold a few training items)
HAMMERS HELD     Gold 3   ✓ recommended: 3
COINS            150      ✓ the finale races pay no coins
```

Per uma.guide, each finale race gives a base +10 to all stats, which is why the hammers matter. Winning also gives a hint for the Radiant Star skill. GameTora says the finale's distance and terrain depend on the races you ran across the three years, but does not give the rule, so it would not be modeled.

Source: `SCENARIO-PUBLISHER-REFERENCES.md` lines 279-298 and 460-478.

---

## 15. What I Could Not Confirm

1. **Short-distance turf checkpoints.** GameTora (page stamped 2026-03-12) says Curren Chan's profile is 60 / 200 / 300, with only the third objective reduced. uma.guide (2026-04-29) says sprinters with weak other aptitudes follow the dirt profile, 30 / 200 / 300. `config/scenarios.php` carries all three tracks (`standard` / `dirt_leaning` / `limited_turf_range`); `TrainingRun::gradeObjectives()` renders only `standard` and says so, because a wrong denominator is worse than a conservative one. Keep the profile editable and confirm in game. KI-15, OPEN.

2. **Stat caps on Global.** RESOLVED. The GameTora data export `scenarios.json` (fetched 2026-09-27, after the 2026-07-01 update) carries Trackblazer `stats: [0, 700, 0, 0, 300]` over the 1200 base, confirming 1200 / 1900 / 1200 / 1200 / 1500 on Global. The config `cap_bonus` matches exactly, with `live_on_global` set to `2026-03-12`.

3. **Names.** GameTora calls it the "Special Shop", and uma.guide calls it the "Coin Shop" or "Climax Store". Do not hard-code a display name until it is read from the client.

4. **Placement modifiers.** RESOLVED. The 100/60/40/20/10% curve is confirmed `[Both]` — uma.guide prints it as a labeled table and GameTora records the same modifiers in prose ("proportionally lower"). Source: `SCENARIO-PUBLISHER-REFERENCES.md` lines 104-110.

5. **Race stat table.** uma.guide's per-grade table gives base figures with Race Bonus and hammer scaling. The page excerpt does not say how the stat figure is split across stats, so treat the unit as unclear until checked.

6. **Alarm Clock retries.** PARTIALLY RESOLVED. SCENARIO-PUBLISHER-REFERENCES.md:1223 confirms the per-career cap is **5 Alarm Clocks** ("the item that re-runs a race you failed to win, up to 5 per career"), not three per run as the wiki stated. The resume point remains unconfirmed: the wording is *that race*, not a period start (UMAMUSUME_REFERENCE.md §2.2.3). Which turn the retry resumes on is unstated by any source.

7. **Finale selection rule, and which year "after late December" means in the fatigue rule.**

What I would not build:

- A fixed "best route" that plays itself. Trainer decides, assistant advises.
- A placement-probability number. The game does not publish one.
- A hard-coded shop tier list presented as a game rule.

---

## MVP Screen Set

1. Trainer Home
2. Trainee & Target
3. Legacy Lab
4. Support Deck Builder
5. Route Planner
6. Run Preflight
7. Career Cockpit (with the Race Desk and Shop drawer)
8. Checkpoint & Finale Briefing
9. Career Report / Veteran

#7 should again get most of the design effort. The route planner is the screen that has no equivalent in Grand Concert.

---

## 16. DECISION CONTRACT

Every recommendation the Advisor produces follows this shape — it must not be a bare action label:

1. **Current state and immediate objective.**
2. **Recommended action** and expected benefit (with numbers).
3. **Best alternative** and its trade-off.
4. **Confidence** — verified, derived, configurable, or unknown — with the source badge.
5. **Conditions that would change the recommendation.**

Example:

> **★ RECOMMENDED: RACE (G2, Mile)**
>
> You need 128 GP this period with 9 turns left. A G2 win is worth 80; the floor (2nd–3rd) is 48.
>
> - **Race G2 Mile:** +80 GP (1st), +100 coins, advances route progress.
> - **Alternative — train Speed:** +stats but 0 GP; checkpoint risk remains.
> - **Confidence: verified** (GP values, `SCENARIO-PUBLISHER-REFERENCES.md` lines 92–99).
> - **Switch if:** fatigue chain hits 3 (60% Mood Down), or a Summer Camp turn opens (bank coins first).

---

## 17. RACE CALENDAR PANEL

Trackblazer is race-driven, so the race calendar deserves a dedicated panel in the cockpit — not just the route planner's period view.

```
RACE CALENDAR                Classic • Turn 31

UPCOMING RACES                             DISTANCE / SURFACE
• G2 Mile (Satsuki Sho qualifier)           Mile / Turf
  → 4 turns                                Win: +80 GP, +100 coins
• G3 Sprint (fill-in)                       Sprint / Turf
  → 2 turns                                Win: +60 GP, +80 coins
• G1 Long (Derby lead-up)                   Long / Turf
  → 18 turns                               Win: +100 GP, +150 coins

CONSEQUENCES OF SKIPPING
• G2 Mile: +1 route gap → Spring Champion route at risk
• G3 Sprint: no impact on epithet progress
• G1 Long: -100 GP from runway, -150 coins from economy

ADVISOR: Race the G2 Mile now. You are 128 GP short with 9 turns left.
```

Source values: GP per grade from §6 (G1 100, G2 80, G3 60), placement modifiers from §6.

---

## 18. SCHEDULE RISK WARNINGS

The cockpit should surface five risk types as flags, each with a severity level:

| Risk                     | Trigger                              | Severity | Mitigation                                  |
| ------------------------ | ------------------------------------ | -------- | ------------------------------------------- |
| Race density             | 3+ consecutive races without rest    | High     | Insert rest turn or use recovery item       |
| Fitness deficit          | 2+ consecutive races at 1 energy      | Medium   | Hold Vita 65 or Good-Luck Charm             |
| GP shortfall             | Floor projection < checkpoint target  | High     | Race higher-grade or use Master Cleat       |
| Coin shortfall           | < 100 entering Summer                 | Medium   | Race an extra G2 before Summer              |
| Epithet decay            | Lost race in an active route          | High     | Grey the route; abandon or replan           |

Source: fatigue table §10, coin reserves §11, GP values §6, epithet rules §12, checkpoint tables SCENARIO-PUBLISHER-REFERENCES.md §7.

---

## 19. RESOURCE LEDGER

A persistent ledger tracks coins, items, and expected income/expense across the remaining career.

```
RESOURCE LEDGER                    Classic • Turn 31

COINS
  Current:          210
  Projected inflow: +180 (2 races)
  Projected cost:   -120 (2 Megaphones + Hammer)
  Summer reserve:   100 (target)

ITEMS
  Good-Luck Charm   1/5   ✓ hold for fatigue risk
  Master Cleat       1/5   ⚠ use before G1 Long (next race)
  Empowering Meg.    2/5   ✓ Summer stock: 2 held, need 2 more

ADVISOR: Hold 100+ coins: Summer starts in 6 turns and the shop resets then.
```

Rules: shop locked until Debut (§11), refresh every 6 turns (§11), 5 copies per item max (§11), Summer Camp window (§12).

---

## 20. ACCEPTANCE CRITERIA

| Feature                    | Success Criterion                                                                 | Edge Case                              |
| -------------------------- | --------------------------------------------------------------------------------- | ---------------------------------------- |
| Race Calendar panel        | Shows next 3 races, distances, GP/coin values, and skip consequences              | No eligible races in upcoming period    |
| Schedule risk warnings     | Flags fatigue chains, GP shortfalls, and coin shortfalls before they bite         | All risks resolved (empty flag set)     |
| Resource ledger            | Tracks coins + 5 key items, forecasts to Summer and Finale                       | Item held at 5/5 cap                    |
| Checkpoint projector       | Shows needed, planned (wins), and floor (2nd–3rd) for each GP period             | Current period already failed           |
| Route tracker              | Greys out routes when a race is lost, tracks remaining per route                  | Rival races random (cannot schedule)    |
| Recommendation reconciliation | Predicted outcome vs actual entry updates run state and flags deviation        | User edits state manually              |

---

## 21. VERIFICATION STATUS

Each feature carries one of three levels — never collapsed:

- **Implemented** — the code exists and the behavior is connected.
- **Verified** — automated tests or reproducible manual checks demonstrate the intended behavior.
- **Game-validated** — calculations have been compared against authoritative mechanics or observed in-game.

A page that renders is not verified. A verified calculation can still rest on an incorrect understanding of the game. This distinction is tracked per feature.

---

## 22. IMPLEMENTATION AND TESTING PLAN

| Phase                      | Deliverable                                                                     | Completion evidence                                              |
| -------------------------- | ------------------------------------------------------------------------------- | ---------------------------------------------------------------- |
| 0. Reconnaissance          | Inspect existing scenario code, plans, contracts, routes, data models and tests | Inventory of existing, partial, missing and conflicting behavior |
| 1. Rules audit             | Document verified scenario mechanics and unresolved assumptions                 | Source-backed rules and explicit unknowns                        |
| 2. State model             | Define run state, transitions, persistence and validation                       | Tests for valid and invalid state changes                        |
| 3. Decision engine         | Implement scenario-specific candidate evaluation and ranking                    | Deterministic tests using known inputs and expected outputs      |
| 4. Command center          | Build the primary screen around the next actionable decision                    | UI tests for loading, empty, error, stale and normal states      |
| 5. Reconciliation          | Compare predicted versus actual outcomes                                        | Tests for updating run state without losing history              |
| 6. End-to-end verification | Exercise complete careers and edge cases                                        | Reproducible scenarios, regression results and completion report |

**Trackblazer focus:** phase 3 builds the Grade Point rules engine (GP per grade, placement modifiers, coin payouts, fatigue odds). Phase 4 builds the Race Desk and Shop drawer. Phase 5 adds reconciliation for race outcomes (predicted GP vs. actual placement). Phase 6 validates checkpoint feasibility against in-game captures.

---

## 23. PRIORITY MATRIX

| Priority | Scope                    | Rationale                                                  |
| -------- | ------------------------ | ---------------------------------------------------------- |
| P0       | Canonical run state, GP rules, regression tests | Protects the trustworthiness of the race-driven scenario     |
| P1       | Next-action recommendations, deadline tracking, risk warnings | Turns Cockpit from display into a command center          |
| P2       | Resource forecasting, route optimization, matchup analysis | Materially affects coin and GP planning                   |
| P3       | Keyboard-friendly controls, richer historical analytics | Convenience only                                           |

---

## Note on Grand Concert Write-Up

The Grand Concert write-up uses GameTora's wording (Hype, Mental, Vocal and Visual). The reference's section 2.9 has Cygames' official Global copy for the same objects (Live Performance Expectations, Composure, Vocals, Visuals). That is worth a pass before it goes into a spec.
