# URA Finale — Scenario Guide (Global EN Server)

**Server:** `[Global]`
**Status:** Active
**Last Verified:** 2026-09-27 (metadata pass; guide carries no stat-cap claims, see `docs/adr/0002` for the measured cap table)
**Superseded By:** none


## Overview

URA Finale is the **first and most basic career (training) scenario** in *Umamusume: Pretty Derby*, and was the only scenario available on Global at launch. It's the "default" mode the rest of the scenarios build on top of, so understanding it thoroughly makes every later scenario easier to learn.

The goal of every scenario, URA Finale included, is the same at its core: take your chosen trainee (Umamusume) through roughly 3 in-game years (Junior, Classic, Senior) of turns, alternating between **training** (raising Speed/Stamina/Power/Guts/Wit), **resting**, **recreation** (mood), and **racing**, while managing Energy and Mood, so that by the end of the career she has strong stats, a good skill set, and a completed Unique Skill.

URA Finale has **no special mid-career mechanic** layered on top of this loop — no team system, no shop, no mid-scenario currency. This is exactly why it's considered the "purest" and most straightforward scenario, and why it's a good baseline to learn the fundamentals of training, mood, energy, and race timing before moving to scenarios with extra systems (Unity Cup) or extra freedom (Trackblazer).

## Scenario Link Character: Aoi Kiryuin

- Aoi Kiryuin is URA Finale's **Scenario Link** character. She'll occasionally show up during training and boosts your stats via her interactions with another character, Happy Meek.
- If you don't yet own many strong Support Cards, running the **SR Aoi Kiryuin** support card is recommended, since it amplifies the effect of any event where Aoi appears.
- This is a low-investment scenario bonus — unlike Unity Cup's Riko Kashimoto or Team Name characters, it doesn't require deckbuilding around her to function.

## Building Your Support Deck

Each Umamusume has an ideal **running style** (Front Runner / Pace Chaser / Late Surger / End Closer) and **distance aptitude** (Sprint / Mile / Medium / Long). Your 6-card support deck should be built around whichever of these your trainee is strongest at. General templates:

| Distance | Build 1 | Build 2 |
|---|---|---|
| Sprint | 4× Speed, 2× Power | 3× Speed, 1× Wit, 2× Power |
| Mile | 4× Speed, 2× Power | 3× Speed, 1× Wit, 2× Power |
| Medium | 4× Speed, 1× Friend/Guts, 1× Power | 3× Speed, 1× Wit, 1× Friend/Guts, 1× Power |
| Long | 4× Speed, 2× Friend/Guts | 3× Speed, 1× Wit, 2× Friend/Guts |

Key principles when picking cards:
- **Check the skills a card provides.** If a card's built-in skill matches your trainee's running style/distance, that skill becomes purchasable during training — this is often as important as the raw stat bonuses the card gives.
- Speed is close to universally valuable regardless of build, since final race performance leans heavily on it; the "flex" slots (Wit, Power, Stamina/Guts) are what you adjust per distance and style.

## Legacy Umamusume (Inheritance)

- A **Legacy** is a previously-completed trainee (yours or borrowed from a friend) that you "inherit" before starting a new career run.
- Pick a Legacy whose stat boosts complement your current trainee's running style/distance needs.
- Legacy skills can also appear in your purchasable skill pool during the run, functioning similarly to support card skills.
- URA Finale gives **three scripted Inspiration events** (Junior Year start, Classic Year early April, Senior Year early April) where stat boosts are granted **based on your Legacy's own build** — so your Legacy choice has a compounding effect across the whole career, not just at the start.

## Core Training Loop Mechanics

### Training Level (Repeat-to-Upgrade)
Unlike scenarios with a team/rank-based facility level, URA Finale's **training facility level increases simply by training the same stat repeatedly** — training level goes up every 4 repetitions of the same stat. Higher training level = higher stat gain per turn, so it is efficient to commit to a stat for several turns in a row rather than constantly switching.

### Mood
Mood has a direct multiplicative effect on your run:
- **Great Mood:** +20% training stat gains, +4% race performance.
- Poor mood reduces both training gains and race performance, and lowers skill activation odds.
- Use **Recreation** to raise mood, at the cost of an entire turn with no training progress — a worthwhile trade before races or before Summer training, but wasteful to overuse.

### Energy & Summer Training
- Each Career year has a **Summer Camp** event (4 turns, roughly Early July–Late August) during which **every stat facility is boosted to Level 5** regardless of your current training level, and Rest/Recreation restore both Energy and Mood simultaneously.
- Because Summer training is so efficient, the single biggest URA Finale mistake is entering Summer with low Energy or bad Mood and wasting those free Level-5 turns on forced rest. **Bank full Energy and Great Mood right before Summer starts.**

### Stamina Requirements
A very common cause of losing URA Finale races despite good other stats is **insufficient Stamina**. Minimum practical Stamina thresholds by distance:

| Distance | Minimum Stamina |
|---|---|
| Sprint | ~350–450 (D–C rank) |
| Mile | ~450–550 (C to C+ rank) |
| Medium | ~600–700 (B rank) |
| Long | ~700–800 (B+ rank) |

Even a stat-maxed trainee will lose if she runs out of stamina mid-race, so treat this as a hard floor, not a "nice to have."

### Fans and Skill Points
- Racing earns **Fans** and **Skill Points**. Skill Points let you buy skills; Fans raise your Fan Tier (affecting final grade) and are also **gate requirements** for certain late-game events that upgrade your Unique Skill.
- Key Fan thresholds (Turf / Dirt) to hit before the associated event:
  - Valentine's Day (Senior, early Feb): 60,000 / 40,000
  - Fan Fest (Senior, early Apr, needs 3-bar friendship w/ Director Akikawa): 70,000 / 60,000
  - Holiday Season (Senior, late Dec): 120,000 / 80,000
- Missing these thresholds doesn't end your run, but it does mean a weaker Unique Skill — so plan a race calendar that reliably clears them.

## Full Fixed Event Calendar

### Junior Year
| Event | Timing/Requirement | Effect |
|---|---|---|
| Inspiration (1st) | Career start | Stat boost, scaled by Legacy Uma |
| Road to Stardom | After 3 turns (pre-debut) | Director Akikawa begins appearing in training |
| Junior Make Debut (race) | After 11 turns | Mandatory debut race |
| After the Debut | Post-debut race | Promotion to Beginner Class; unlocks other races |
| A Quirky Respondent? | Early July (last turn) | Reporter Etsuko Otonashi begins appearing in training |

### Classic Year
| Event | Timing/Requirement | Effect |
|---|---|---|
| New Year's Resolutions | Early Jan (start of turn) | Choice: Stat +10 / Energy +20 / Skill Pts +20 |
| Holding an Event! | Early March (end of turn) | Mood up |
| Inspiration (2nd) | Early April | Stat boost, scaled by Legacy Uma |
| Summer Camp Begins → Ends | Early July → Late Aug (4 turns) | All facilities Lv.5; Rest/Recreation restore Energy+Mood; ends with +5 to 3 random stats |
| A Three-Legged Race | Early Nov (end of turn), 50,000 fans | Hint for Iron Will Lv.1; +20 Skill Pts; +20 Wit |
| At the Carrot Farm | Late Dec, 100,000 fans | +30 Skill Points |

### Senior Year
| Event | Timing/Requirement | Effect |
|---|---|---|
| New Year's Shrine Visit | Early Jan (start of turn) | Choice: Energy +30 / All Stats +5 / Skill Pts +35 |
| Raffle Time! | Early Jan (end of turn) | Random: all stats up / energy+mood up / energy only / mood down |
| Valentine's Day | Early Feb, 60k(Turf)/40k(Dirt) fans | Unique Skill level up |
| Inspiration (3rd) | Early April | Stat boost, scaled by Legacy Uma |
| Fan Fest | Early April, 70k(Turf)/60k(Dirt) fans + 3-bar Akikawa friendship | Unique Skill level up + Mood up |
| Summer Camp Begins → Ends | Early July → Late Aug (4 turns) | Same as Classic Year Summer Camp |
| Holiday Season | Late Dec, 120k(Turf)/80k(Dirt) fans | Unique Skill level up |
| Going to the URA Finale! | Late Dec | +30 Skill Points |
| After URA Finale Qualifier | Win qualifier | All stats +10, +40 Skill Pts, affected by Race Bonus |
| After URA Finale Semifinals | Win semis | All stats +10, +60 Skill Pts, affected by Race Bonus |
| After URA Finale Finals | Win finals | All stats +10, +80 Skill Pts, affected by Race Bonus |
| Twinkle Monthly Special Issue | After URA Finale races, max friendship w/ Etsuko Otonashi | All stats +5, +20 Skill Pts |
| A Super Successful Event! | Win Finals + max friendship w/ Director Akikawa | Director's Fan item, all stats +15, +50 Skill Pts |
| Individual Trainee Event | Win Finals | Good ending; all stats +5, +20 Skill Pts |

## Best Strategy Summary for URA Finale

1. **Build your deck around one clear identity** (distance + running style) rather than a generic "balanced" deck — the training-level and skill-pool synergy rewards specialization.
2. **Pick a Legacy that reinforces the same identity**, since three separate Inspiration events scale off it across the whole run.
3. **Repeat the same stat training in blocks of 4** to climb training level efficiently, rather than round-robining every stat every turn.
4. **Protect Mood before races and before Summer Camp** — Recreation turns spent right before these payoff windows are far more valuable than Recreation spent randomly.
5. **Track your Fan count against the Senior Year thresholds** (60k/70k/120k Turf, lower for Dirt) well in advance, since these determine whether your Unique Skill actually finishes upgrading.
6. **Never dip below the distance-appropriate Stamina floor** — treat Stamina as a hard prerequisite, not a flex stat, especially for Medium/Long builds.
7. Since URA Finale has no scenario-specific currency or team mechanic to lean on, **skill purchases from Skill Points are your main "build" lever** beyond raw stats — prioritize skills matching your running style and any final-corner/last-spurt skills, which are broadly strong regardless of build.
