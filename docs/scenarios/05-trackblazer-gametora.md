# Trackblazer Scenario — GameTora Reference Guide

**Server:** `[Global]`
**Status:** Active
**Last Verified:** 2026-09-27 (metadata pass; source page last updated 2026-03-12, the Global launch day)
**Superseded By:** none


*Source: gametora.com/umamusume/trackblazer — By Gertas & robflop. Last updated 2026-03-12 (scenario's Global launch day).*

> This is GameTora's precise mechanics/data reference for Trackblazer, meant to complement uma.guide's strategy-and-deck-focused write-up (see the separate uma.guide file) and the general overview in the original three-scenario document set. Where numbers differ slightly between sources, that's normal — GameTora tracks exact patch-accurate values, while community strategy guides sometimes round or generalize.

## Basic Information

- **Trackblazer** (JP: *Make a New Track*) released on Global **March 12, 2026**, originally released February 24, 2022 on JP. It is the **third permanent scenario**, after URA Finals and Unity Cup.
- Its mechanics are substantially different from the prior two scenarios — you do **not** need any Unity Cup knowledge to play Trackblazer, though general game fundamentals still apply.
- The scenario is built entirely around **Grade Points** collected via racing, replacing character-specific career objectives entirely. This means **no race is off-limits due to a scripted objective conflict**, and you can pursue **every race-related trophy** in the game within a single scenario, unlike URA Finale/Unity Cup's locked schedules.
- There are **4 objectives total**:
  1. Participate in the Debut race.
  2. Collect 60 Grade Points.
  3. Collect 300 more Grade Points.
  4. Collect a final 300 more Grade Points.
- **Aptitude-based exceptions** to the point requirements:
  - High-dirt/low-turf characters (e.g. **Haru Urara**): objective 2 requires only **30** Grade Points (not 60), and objective 3 requires only **200** (not 300).
  - Turf characters with poor aptitude outside short distances (e.g. **Curren Chan**): objective 3 requires **200** Grade Points (not 300).
- **Surplus points from a completed objective do NOT carry over** to the next one — you start each new objective period at zero.
- Unlike Unity Cup and URA Finale, Trackblazer has **no new original story characters and no Scenario Link mechanic** attached to it.

## Grade Points and Shop Coins — Exact Values

Grade Points scale with **race grade**, not the specific race, and both Grade Points and Shop Coins scale down proportionally the lower you place (similar to how Fan gain scales):

| Race Grade | Grade Points (1st place) |
|---|---|
| G1 | 100 |
| G2 | 80 |
| G3 | 60 |
| OP | 40 |
| Pre-OP | 20 |

| Placement | Shop Coins |
|---|---|
| 1st | 100 |
| 2nd or 3rd | 60 |
| 4th or 5th | 30 |
| 6th and lower | 0 |

Note: **Shop Coins do not depend on race grade at all** — a Pre-OP win and a G1 win both pay 100 coins for 1st place; only Grade Points scale with grade.

## Special Shop Mechanics

- Locked until after the Debut race.
- **Lineup refreshes every 6 turns** (a timer is viewable in the Special Shop menu).
- **You cannot hold more than 5 copies of a single item at once.**
- The shop can occasionally offer:
  - **Limited items** — separate availability window from the main rotation, flagged top-right on the shop button.
  - **Sales** — 10–20% discounts on the current rotation, flagged top-left on the shop button.
- Items lasting multiple turns **cannot be reused while already active**; using a lower-grade version of an effect and then a higher-grade version **overwrites** the lower one (wasting it) — but using the higher-grade one first blocks using the lower-grade one until it expires.
- **Training facility level items and bond items are permanent for the run** and cannot be lost. Status-condition items persist until removed some other way (e.g. losing a "Good Training" condition by failing a training).

## Full Shop Item List (Exact Costs & Effects)

### Stats
| Item | Cost | Effect |
|---|---|---|
| Speed / Stamina / Power / Guts / Wit Notepad | 10 | +3 to the specific stat |
| Speed / Stamina / Power / Guts / Wit Manual | 15 | +7 to the specific stat |
| Speed / Stamina / Power / Guts / Wit Scroll | 30 | +15 to the specific stat |

### Energy and Motivation
| Item | Cost | Effect |
|---|---|---|
| Vita 20 | 35 | Energy +20 |
| Vita 40 | 55 | Energy +40 |
| Vita 65 | 75 | Energy +65 |
| Royal Kale Juice | 70 | Energy +100, Motivation −1 |
| Energy Drink MAX | 30 | Max Energy +4, Energy +5 |
| Energy Drink MAX EX | 50 | Max Energy +8 |
| Plain Cupcake | 30 | Motivation +1 |
| Berry Sweet Cupcake | 55 | Motivation +2 |

### Bond
| Item | Cost | Effect |
|---|---|---|
| Yummy Cat Food | 10 | Yayoi Akikawa's bond +5 |
| Grilled Carrots | 40 | All support card bonds +5 |

### Get Good Conditions
| Item | Cost | Effect |
|---|---|---|
| Pretty Mirror | 150 | Grants a "Get [good status]" effect |
| Reporter's Binoculars | 150 | Grants a "Get [good status]" effect |
| Master Practice Guide | 150 | Grants a "Get [good status]" effect |
| Scholar's Hat | 280 | Grants "Fast Learner" status effect |

### Heal Bad Conditions
| Item | Cost | Effect |
|---|---|---|
| Fluffy Pillow / Pocket Planner / Rich Hand Cream / Smart Scale / Aroma Diffuser / Practice Drills DVD | 15 each | Heals a specific bad condition |
| Miracle Cure | 40 | Heals ALL negative status effects |

### Training Facilities
| Item | Cost | Effect |
|---|---|---|
| Speed / Stamina / Power / Guts / Wit Training Application | 150 | Raises that Training Facility Level by 1, permanently for the run |
| Reset Whistle | 20 | Shuffles support card distribution |

### Training Effects
| Item | Cost | Effect |
|---|---|---|
| Coaching Megaphone | 40 | Training bonus +20% for 4 turns |
| Motivating Megaphone | 55 | Training bonus +40% for 3 turns |
| Empowering Megaphone | 70 | Training bonus +60% for 2 turns |
| Speed / Stamina / Power / Guts Ankle Weights | 50 each | +50% training bonus for that stat, +20% energy consumption (1 turn) — note: **no Wit version exists** |
| Good-Luck Charm | 40 | Training failure rate set to 0% for 1 turn |

### Races
| Item | Cost | Effect |
|---|---|---|
| Artisan Cleat Hammer | 25 | Race bonus +20% (1 turn) |
| Master Cleat Hammer | 40 | Race bonus +35% (1 turn) |
| Glow Sticks | 15 | Race fan gain +50% (1 turn) |

## Rivals

- A random race may be marked with a **red/blue "VS" speech-bubble icon** and shimmer effect on the race button — this indicates a Rival race is available.
- **Winning (1st place)** vs. the rival grants **one random skill hint**, tied to either the race's distance or the running style you used.
- Scoring follows the same convention as Legend Races: placing 1st = win; placing better than the rival but not 1st = draw; placing worse = loss.

## Twinkle Star Climax (The Finale)

- Consists of **3 individual races**, structured as a **mini-league / points leaderboard**, not an elimination bracket like URA Finale's finals.
- You do **not** need to win all 3 — you only need the **most Victory Points** by the end of the third race.
- Max 10 Victory Points per race (1st place), so a maximum of **30 total** across all three.
- Distance and terrain for these races are determined by the races you ran throughout the career, same principle as URA Finale/Unity Cup finals.

| Placement | Victory Points |
|---|---|
| 1st | 10 |
| 2nd | 8 |
| 3rd | 6 |
| 4th | 4 |
| 5th–6th | 3 |
| 7th–9th | 2 |
| 10th–13th | 1 |
| 14th+ | 0 |

Winning the Climax overall awards a hint for a specific finale skill; separate hints for its normal (non-climax) version can also come from unique-skill level-up training events earlier in the run.

## Unique Skill Level-Ups

Distinct mechanic from both URA Finale and Unity Cup:

- A **"Umamusume of the Year" (Junior/Classic/Senior)** training event occurs in **late December of each year**.
- If your trainee is selected, her Unique Skill levels up and she receives a skill hint.
- Selection appears to depend on **Chairman Yayoi Akikawa's bond level** combined with some mix of **fan count and races won that year** (exact fan/race thresholds are not fully confirmed) — GameTora's advice is simply to "win a few races every year" to be safe.
- Confirmed **bond level** requirements by year:

| Year | Required Akikawa Bond |
|---|---|
| Junior (1st) | Blue bond, 1 bar |
| Classic (2nd) | Blue bond, 2 bars |
| Senior (3rd) | Green bond, 3 bars |

## Base Training Values (Facility Level 1, No Supports)

| Facility | Stat gains | Energy |
|---|---|---|
| Speed | +8 Speed, +4 Power, +2 SP | −19 |
| Stamina | +7 Stamina, +3 Guts, +2 SP | −17 |
| Power | +4 Stamina, +6 Power, +2 SP | −18 |
| Guts | +3 Speed, +3 Power, +6 Guts, +2 SP | −20 |
| Wisdom | +2 Speed, +6 Wisdom, +3 SP | +5 |

## Training Levels

- Same underlying rule as URA Finale: facilities start at Level 1 and **level up every 4 uses**, max Level 5.
- **New for Trackblazer:** Training Facility level can *also* be permanently raised by **1** via the Special Shop's Training Application items (150 coins each), stacking on top of natural use-based leveling — a mechanic URA Finale does not have.

## Character-Specific Events

- **Secret character events cannot trigger in Trackblazer** — this includes things like extra Triple Crown-specific rewards tied to a particular character. Confirms the same restriction uma.guide notes (e.g. Silence Suzuka's Runaway style).

## Scenario-Specific Epithets

- Achieving a Trackblazer-specific epithet triggers a special event with Chairman Akikawa Yayoi and grants bonuses. GameTora maintains the full epithet list on a separate page (gametora.com/umamusume/nicknames) rather than duplicating it on the scenario page itself — cross-reference the uma.guide Trackblazer document in this set for the detailed epithet/route tables.

## Scenario Factor (Inheritance)

- Trackblazer's scenario factor is called **"Climax Scenario"**, and grants **Stamina and Guts** bonuses on successful inheritance to a future trainee — this is the Legacy-passing bonus specific to this scenario, similar in concept to URA Finale's and Unity Cup's own scenario sparks.

## Stat Caps

- **On Global, Trackblazer is expected to launch with the standard 1200/1200/1200/1200/1200 caps** (same as the universal base cap), rather than the higher JP-specific caps.
- On other (non-Global) servers, Trackblazer's base stat caps are higher:

| Stat | Cap (non-Global) |
|---|---|
| Speed | 1200 |
| Stamina | 1900 |
| Power | 1200 |
| Guts | 1200 |
| Wisdom | 1500 |

Global players should not assume the higher Stamina/Wisdom caps apply immediately — check current patch notes, as this may be adjusted post-launch (Unity Cup received a similar later adjustment on Global, see the Unity Cup GameTora file in this set).

## Quick-Reference Differences vs. URA Finale / Unity Cup

| Aspect | URA Finale | Unity Cup | Trackblazer |
|---|---|---|---|
| Progress goal | Scripted race-goal calendar | Scripted race-goal calendar + team races | Grade Point thresholds (self-paced racing) |
| Facility leveling | Repeat same stat 4x | Team stat rank | Repeat same stat 4x **+** shop items |
| Special currency | None | None | Shop Coins |
| Secret events | Enabled | Enabled | **Disabled** |
| New team/social mechanic | None | Unity Training / Spirit Bursts | None (solo, race-economy focus) |
| Scenario Link character | Aoi Kiryuin | (none — see Unity Cup file for Team Name characters) | None |
