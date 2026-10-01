# Scenario Publisher References

**This is the consolidated reference for the owner-supplied Trackblazer and Unity Cup publisher guides (uma.guide and GameTora). Do not create new files for this topic. Update this file instead.**

## Provenance

Embedded verbatim from `docs/scenarios/04-trackblazer-umaguide.md`, `docs/scenarios/05-trackblazer-gametora.md`, and `docs/scenarios/06-unity-cup-gametora.md`. Each original heading is demoted by one level under its source section. No content was summarised, deduplicated, or resolved; the three sources keep their own server/date qualifiers and any contradiction is recorded in the Disagreements section.

## Disagreements

The three merged sources contain one live contradiction that the app carries as KI-15. It is documented here rather than resolved, because resolving it is a schema/owner decision (CONSTRAINTS escalation path 4/7), not a merge call:

- **Which Grade Point track a limited-range trainee uses.** The uma.guide Trackblazer file puts `Sprint Umas with poor aptitude in other distances'' on the **Dirt** requirement track. The GameTora Trackblazer file gives a turf character with poor aptitude outside short distances a **third** track in which only the Classic objective drops to 200. Neither names the aptitude letter that places a trainee in a track. `config/scenarios.php` carries all three tracks (`standard` / `dirt_leaning` / `limited_turf_range`); `TrainingRun::gradeObjectives()` renders only `standard` and says so, because a wrong denominator is worse than a conservative one. Tracked as **KI-15**, OPEN.
- **Stat-cap currency across sources.** The GameTora Trackblazer file (dated on the scenario launch day) predicts Global keeps flat 1200 caps; the later GameTora Unity Cup file confirms the post-2026-07-01 raised caps. This is a date conflict, not a merge conflict, and is governed by `docs/adr/0002` and `docs/adr/0003`; both statements are kept verbatim so the expiry is visible. See DESIGN-CORPUS D-228.

---

## Trackblazer (uma.guide)

## Trackblazer (MANT) — uma.guide Community Guide

**Server:** `[Global]`
**Status:** Active
**Last Verified:** 2026-09-27 (metadata pass; source page last updated 2026-04-29)
**Superseded By:** none


*Source: uma.guide/guides/trackblazer — Contributors: ayan, catbomb, KoYu1, Luseless, Charles, and others. Last updated Apr 29, 2026.*

> This document captures uma.guide's community strategy take on Trackblazer, which is complementary to the more mechanics-first GameTora breakdown (see the separate GameTora file). Trackblazer launched on Global **March 12, 2026** as the third permanent scenario, alongside URA Finale and Unity Cup — all three remain selectable going forward.

### Deck Archetypes

uma.guide frames Trackblazer decks around two core frameworks (flexible, adjust per Uma's growth rates and skill needs):

#### 1. Speed + Wit
- Base shape: **2 Speed + 2 Wit cards**, with 2 flexible slots.
- Flex slots are commonly filled with **Power cards that carry good skills** (e.g. Yaeno Muteki, El Condor Pasa, Smart Falcon).
- A free-to-play variant uses a borrowed Fine Motion friend card plus 2 flex slots.
- Other variants swap flex slots for Stamina cards if Stamina requirements are a concern for the build.
- **Tip:** Stay at or above 50% Race Bonus for consistent results.

#### 2. Wit + Guts
- Base shape: **3 Guts + 2 Wit cards**, with an optional 4th Guts card or a Speed card depending on need.
- Main idea: rely mostly on **Guts Anklets** (a training item) rather than needing many different item types, unlike Speed+Wit's broader item spread.
- Can fully cap Guts, which Speed+Wit decks typically can't.
- **Downsides:** harder to source skills through Guts cards (puts more weight on Legacy inheritance for skills), and can struggle to hit Stamina requirements for longer races.
- SSR Haru Urara (Guts) is a strong card for this archetype, but starts at **0 Initial Friendship** — budget extra bond-building time for her.

### Races Dominate the Turn Economy

- A typical Trackblazer career spends **roughly half its turns racing** — commonly **30–40 races** across a full run, far more than URA Finale or Unity Cup.
- This is because, unlike the two earlier scenarios, races carry **extra rewards beyond SP and stats**: Grade Points and Shop Coins (see below).
- ⚠️ **Career Goals and Secret Events are disabled in Trackblazer.** Concrete named examples: Silence Suzuka cannot acquire the Runaway running style, and Mejiro Ardan cannot be inflicted with Glass Legs. If a build depends on one of these locked outcomes, don't run that character in Trackblazer for that purpose.

### Grade Points (Replacing Career Goals)

Trackblazer swaps individual Uma career goals for **4 shared objectives**:

| Deadline | Turf Requirement | Dirt Requirement |
|---|---|---|
| Late June, Junior Year | Debut Race | Debut Race |
| End of Junior Year | 60 Grade Points | 30 Grade Points |
| End of Classic Year | 300 Grade Points | 200 Grade Points |
| End of Senior Year | 300 Grade Points | 300 Grade Points |

- Sprint Umas with poor aptitude in other distances follow the **Dirt** requirement track instead of Turf.
- ⚠️ **All Grade Points are consumed at each deadline and do NOT carry over** — you cannot "bank" surplus points into the next objective period.

**Grade Points earned per 1st-place finish, by race grade:**

| Race Grade | Grade Points (1st) |
|---|---|
| G1 | 100 |
| G2 | 80 |
| G3 | 60 |
| OP | 40 |
| Pre-OP | 20 |

**Placement modifier** (applies to the base value above):

| Placement | Modifier |
|---|---|
| 1st | 100% |
| 2nd | 60% |
| 3rd | 40% |
| 4th–5th | 20% |
| 6th+ | 10% |

### Shop Coins

- Awarded by placement, **regardless of race grade** — unlike Grade Points, a Pre-OP win pays the same Shop Coins as a G1 win.

| Placement | Shop Coins |
|---|---|
| 1st | 100 |
| 2nd–3rd | 60 |
| 4th–5th | 30 |
| 6th+ | 0 |

- Core gameplay loop: **race for coins → spend coins on shop items → use items to boost upcoming training/races**. Winning races can also refresh/add new items to the shop's rotation.

### Epithets (Race Route Bonuses)

Winning **every race in a named route** grants bonus stats or skill hints. uma.guide highlights these as the routes that most shape your racing schedule:

#### Tiara Route (the Oka Sho / Japanese Oaks / Shuka Sho line)
| Epithet | Requirement | Reward |
|---|---|---|
| Lady | Win Oka Sho, Japanese Oaks, Shuka Sho | +10 to 2 random stats |
| Heroine | Lady + win Queen Elizabeth II Cup (Classic) | +10 to 2 random stats |
| Goddess | Lady + win Victoria Mile, Hanshin Juvenile Fillies, both QEII Cups | +15 to 2 random stats |
| Mile a Minute | Win all unique Mile Turf G1s | Mile Straightaways hint +1 |

#### Classic Route
| Epithet | Requirement | Reward |
|---|---|---|
| Stunning | Win Satsuki Sho, Japanese Derby, Kikuka Sho | +10 to 2 random stats |
| Incredible | Stunning + win Japan Cup (Classic) or Arima Kinen (Classic) | +15 to 2 random stats |
| Phenomenal | Stunning + win 2 of: Tenno Sho Spring, Takarazuka Kinen, Japan Cup, Tenno Sho Autumn, Osaka Hai, Arima Kinen | +15 to 2 random stats |

#### Sprint/Mile Route
| Epithet | Requirement | Reward |
|---|---|---|
| Breakneck Miler | Win NHK Mile Cup, Yasuda Kinen, Mile Championship | +15 to 2 random stats |
| Sprint Go-Getter | Win Takamatsunomiya Kinen, Sprinters Stakes | +10 to 2 random stats |
| Sprint Speedster | Win all 4 of the above sprint/mile races | +15 to 2 random stats |

#### Spring/Autumn Route
| Epithet | Requirement | Reward |
|---|---|---|
| Spring Champion | Win Osaka Hai, Tenno Sho Spring, Takarazuka Kinen | +10 to 2 random stats |
| Fall Champion | Win Tenno Sho Autumn, Japan Cup (Senior), Arima Kinen (Senior) | +10 to 2 random stats |
| Shield Bearer | Win Tenno Sho Spring + Autumn | +10 to 2 random stats |
| Legendary | (Lady or Stunning) + both Champion epithets | Homestretch Haste hint +1 |

#### Dirt Route
| Epithet | Requirement | Reward |
|---|---|---|
| Dirty Work | Win 5 Dirt races | +5 to 2 stats |
| Playing Dirty | Win 10 Dirt races | +10 to 2 stats |
| Eat My Dust | Win 15 Dirt races | +10 to 2 stats |
| Dirt G1 Achiever | Win 3 Dirt G1s | +10 to 2 stats |
| Dirt G1 Star | Win 4 Dirt G1s | +10 to 2 stats |
| Dirt G1 Powerhouse | Win 5 Dirt G1s | +15 to 2 stats |
| Dirt G1 Dominator | Win 9 Dirt G1s | Top Pick hint +1 |
| Dirt Sprinter | Win both JBC Sprints | +10 to 2 stats |
| Kicking Up Dust | Win Unicorn S., Leopard S., Japan Dirt Derby | +5 to 2 stats |

⚠️ **Aptitude warning:** Most graded races in the game are Mile and Medium distance. Umas run in Trackblazer should have **at least C, ideally B, Mile and Medium aptitude** or you'll miss out on a large share of available stat/epithet rewards. Distribution of unique graded races:

| Distance | G1 Total | G2 Total | G3 Total |
|---|---|---|---|
| Sprint | 3 | 6 | 18 |
| Mile | 10 | 12 | 33 |
| Medium | 14 | 13 | 17 |
| Long | 3 | 5 | 1 |

#### Secondary Epithets (Lower Priority)
Win-count or location-based (e.g. "Win 3 races in the Kanto Region," "Win 3+ races with 'Junior Stakes' in the name") — generally acquired naturally as a byproduct of normal racing rather than deliberately targeted. Full list includes Pro Racer, Standard/Non-Standard Distance Leader, regional conqueror titles (Kanto, Kansai/West Japan, Hokkaido, Tohoku, Kokura), Junior Jewel, Umatastic, Globe-Trotter, and Turf Tussler.

### Rival Races

- Appear randomly starting **Early August, Junior Year**, marked by a red/blue "VS" icon on the race button.
- Simply participating has a chance to **add new items to the Shop**; **winning** (placing 1st) increases those odds further and additionally grants either a skill hint or +5 to 2 random stats.
- Same win/draw logic as Team Trials and Unity Cup: only 1st place counts as a win; if neither side takes 1st, it's a draw.
- **Key distinction from Unity Cup:** Rival Race skill hints are based on **distance + running style used to win**, not on aptitude the way Unity Cup's Spirit Bursts work.

### Shop Item Priorities (uma.guide's "Worth" Ratings)

| Category | Notable items | uma.guide's take |
|---|---|---|
| Stats | +15 stat (30 coins), +7 stat (15 coins) | Both **Must Buy** |
| Stats | +3 stat (10 coins) | Low priority filler only |
| Mood | +1 Mood (30c), +2 Mood (55c) | Both Must Buy; +1 has priority |
| Energy | +20/+40/+65 Energy | All Must Buy |
| Energy | +100 Energy / −1 Mood (70c) | Must Buy — pair with a Mood item to offset the downside |
| Energy | Max Energy boosters | Generally **Skip** (small one, +5E/+4MaxE, is situational) |
| Bond | +5 all support bonds (40c) | Must Buy if key bonds are missing |
| Bond | +5 Director Akikawa bond (10c) | Skip |
| Race | +35%/+20% Race Bonus (Hammers) | Both Must Buy |
| Race | +50% Fan Bonus | Situational — good with Narita Top Road under 200k fans |
| Training | +60%/+40% Training Bonus (Megaphones) | 60% Must Buy; 40% only if lacking 60% ones |
| Training | +20% Training Bonus | Skip |
| Utility | Reset support card shuffle | Must Buy |
| Utility | +50% single-stat training / +20% energy cost | Must Buy for your priority stat (no Wit version exists) |
| Utility | 0% training failure for 1 turn | Must Buy |
| Status | Heal all conditions | Optional; worth holding one |
| Status | Heal "Skin Outbreak" specifically | Recommended to hold at least one — Skin Outbreak is common in Trackblazer |
| Status | Fast Learner status (280c) | Generally a trap — spending the same coins on Hammers across the run yields more effective SP; having enough coins to buy it outright is described as "generally not a good sign" |
| Status | Charming status (150c) | Only worth it if offered in the very first shop |
| Facility | +1 training facility level (150c) | Only with coin surplus; Wit slightly favored since it lacks an Anklet-equivalent item |

### Gameplay Flow & Race Fatigue

The central ongoing decision in Trackblazer: **train or race?**
- Training remains the best raw stat source; outside G1 turns, races can often be skipped for an exceptionally good training turn — even G1s can be skipped if it doesn't cost you an epithet and training is excellent.
- **Race Fatigue Event** risk rises the more races you run consecutively:

| Consecutive races | Mood Down (1+ Energy) | 0 Energy variant | Skin Outbreak (1+E) | Skin Outbreak (0E) | 3 Random Stat −10 |
|---|---|---|---|---|---|
| 1 race | 0% | 15% | 0% | 4% | 0% |
| 2 races | 0% | 33% | 0% | 8% | 0% |
| 3 races | 60% | 90%+ | 15% | 25% | 0% |
| 4+ races | 100% | 100% | 33% | 33% | 40% |

- Race Fatigue Events **cannot occur after Late December** (i.e. not a concern in the endgame stretch).
- Mood only affects stats by **2% per level**, and many races grant Mood via Reporter Events — so uma.guide suggests **holding Cupcakes (Mood items) until right before a Training turn** rather than using them reactively after every race.
- Guaranteed Energy/Mood events exist as natural mitigation:

| Classic Early Feb | Classic Early Mar | Classic Late Sep | Senior Late Jun | Senior Late Oct | Senior Late Dec |
|---|---|---|---|---|---|
| +1 Mood | +20 Energy | +1 Mood | +20 Energy | +1 Mood | +30 Energy |

#### Pre-Debut (Junior Year)
- Spend turns raising Support Card bond and facility levels, same as other scenarios.
- **Wit is the hardest stat to raise in Trackblazer** — prioritize its facility pre-debut.
- Use the "BBQ"-type bond item evenly across cards under 80 bond for maximum value per purchase (its value diminishes with each already-high-bond card it touches).
- ⚠️ The Debut Race itself **costs Energy** in Trackblazer — budget for it.

#### Post-Debut
- Check both Training and Race options every turn, and track the current Shop rotation.
- Keep building Support Card bonds when not racing.

#### Classic/Senior Year
- Continue bond-building until Classic Year Summer to unlock Friendship Training.
- Base facility values match Unity Cup's (lower than URA Finale's), and combined with races paying more, **lone Friendship Trainings often aren't worth skipping a Graded Race for**.
- Base training values (Level 1, no supports):

| Facility | Stat gains | Energy |
|---|---|---|
| Speed | +8/+4/+2 SP | −19 |
| Stamina | +7/+3/+2 SP | −17 |
| Power | +4/+6/+2 SP | −18 |
| Guts | +3/+3/+6/+2 SP | −20 |
| Wit | +2/+6/+3 SP | +5 |

- Facilities level more slowly on average (fewer training turns overall), which makes **Summer turns (all facilities Level 5) especially valuable**.
- **Tip:** Keep at least 100 coins banked heading into Summer, since the shop resets on Summer's first turn — unless the current rotation is exceptional and worth spending down first.

#### Summer Strategy
- Stockpile Training Items, Energy Items, and at least 2 Megaphones (they last 2 turns) before Classic Year Summer.
- If a Summer training roll isn't strong enough, use Whistles (support card reshuffle) to try for better; if it is strong, spend Training Items to amplify it further.
- If out of Whistles and the roll is still weak, consider racing instead — it has a chance to refresh the shop with new items for the remaining Summer turns.

### Twinkle Star Climax (The Finale)

- Replaces the URA Finale-style elimination finals with a **3-race points league** — you don't need to win all 3, just accumulate more total points than the other competing Umas.

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

- Structured like URA Finale's finale: 3 Training turns + 3 Race turns.
- Save a few Training Items for one last stat push during these 3 training turns.
- Save **3 Golden Hammers** (Race Bonus items) for the 3 Climax races — even though these races give less SP than a typical G1, they grant a flat **+10 to all stats** at base, making Hammers unusually valuable here. Alternatively, 2 Hammers can be saved if you're fairly confident the final shop rotation will offer one, though this is described as a riskier play — banking Gold Hammers from Senior Summer onward is the safer default.
- Save roughly **150 coins** for the final shop, since Twinkle Star Climax races themselves **do not reward Shop Coins**.
- Winning the Climax grants hints for the **Radiant Star** skill.

### Unique Skill Level-Up Requirements

Unlike URA Finale/Unity Cup's pure Fan-count gates, Trackblazer's Unique Skill level-ups (per uma.guide) require **both** Fans and Director Yayoi Akikawa bond level:

| Checkpoint | Fans Required | Akikawa Bond Required |
|---|---|---|
| After Late Dec, Junior Year | 5,000 | 19 (just under 1 full bar) |
| After Late Dec, Classic Year | 60,000 | 31 (slightly above 1.5 bars) |
| After Late Dec, Senior Year | 120,000 | 51 (slightly above 2.5 bars) |

### Best Strategy Summary (uma.guide)

1. **Pick Speed+Wit for flexibility and easier skill sourcing, or Wit+Guts if you want a simpler item economy and can live with weaker Legacy-independent skill access.**
2. **Verify your Uma has at least C (ideally B) Mile and Medium aptitude before committing her to a Trackblazer run** — these two distances dominate the graded race pool this scenario is built around.
3. **Don't over-fixate on racing every available slot** — training is still the primary stat engine; race when it serves an epithet route, Grade Point deadline, or the shop is worth farming coins for.
4. **Bank coins and Hammers ahead of both Summer and the Twinkle Star Climax** rather than spending reactively turn-to-turn.
5. **Treat the Fast Learner shop item with suspicion** — it's a coin sink that usually loses to just buying more Hammers across the run.
6. **Track Grade Point deadlines carefully since points don't carry over** — under-shooting one deadline can't be fixed by over-shooting the next.
7. **Avoid running secret-event-dependent builds** (e.g. Silence Suzuka for Runaway) in this scenario, since those triggers are disabled entirely.

---

## Trackblazer (GameTora)

## Trackblazer Scenario — GameTora Reference Guide

**Server:** `[Global]`
**Status:** Active
**Last Verified:** 2026-09-27 (metadata pass; source page last updated 2026-03-12, the Global launch day)
**Superseded By:** none


*Source: gametora.com/umamusume/trackblazer — By Gertas & robflop. Last updated 2026-03-12 (scenario's Global launch day).*

> This is GameTora's precise mechanics/data reference for Trackblazer, meant to complement uma.guide's strategy-and-deck-focused write-up (see the separate uma.guide file) and the general overview in the original three-scenario document set. Where numbers differ slightly between sources, that's normal — GameTora tracks exact patch-accurate values, while community strategy guides sometimes round or generalize.

### Basic Information

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

### Grade Points and Shop Coins — Exact Values

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

### Special Shop Mechanics

- Locked until after the Debut race.
- **Lineup refreshes every 6 turns** (a timer is viewable in the Special Shop menu).
- **You cannot hold more than 5 copies of a single item at once.**
- The shop can occasionally offer:
  - **Limited items** — separate availability window from the main rotation, flagged top-right on the shop button.
  - **Sales** — 10–20% discounts on the current rotation, flagged top-left on the shop button.
- Items lasting multiple turns **cannot be reused while already active**; using a lower-grade version of an effect and then a higher-grade version **overwrites** the lower one (wasting it) — but using the higher-grade one first blocks using the lower-grade one until it expires.
- **Training facility level items and bond items are permanent for the run** and cannot be lost. Status-condition items persist until removed some other way (e.g. losing a "Good Training" condition by failing a training).

### Full Shop Item List (Exact Costs & Effects)

#### Stats
| Item | Cost | Effect |
|---|---|---|
| Speed / Stamina / Power / Guts / Wit Notepad | 10 | +3 to the specific stat |
| Speed / Stamina / Power / Guts / Wit Manual | 15 | +7 to the specific stat |
| Speed / Stamina / Power / Guts / Wit Scroll | 30 | +15 to the specific stat |

#### Energy and Motivation
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

#### Bond
| Item | Cost | Effect |
|---|---|---|
| Yummy Cat Food | 10 | Yayoi Akikawa's bond +5 |
| Grilled Carrots | 40 | All support card bonds +5 |

#### Get Good Conditions
| Item | Cost | Effect |
|---|---|---|
| Pretty Mirror | 150 | Grants a "Get [good status]" effect |
| Reporter's Binoculars | 150 | Grants a "Get [good status]" effect |
| Master Practice Guide | 150 | Grants a "Get [good status]" effect |
| Scholar's Hat | 280 | Grants "Fast Learner" status effect |

#### Heal Bad Conditions
| Item | Cost | Effect |
|---|---|---|
| Fluffy Pillow / Pocket Planner / Rich Hand Cream / Smart Scale / Aroma Diffuser / Practice Drills DVD | 15 each | Heals a specific bad condition |
| Miracle Cure | 40 | Heals ALL negative status effects |

#### Training Facilities
| Item | Cost | Effect |
|---|---|---|
| Speed / Stamina / Power / Guts / Wit Training Application | 150 | Raises that Training Facility Level by 1, permanently for the run |
| Reset Whistle | 20 | Shuffles support card distribution |

#### Training Effects
| Item | Cost | Effect |
|---|---|---|
| Coaching Megaphone | 40 | Training bonus +20% for 4 turns |
| Motivating Megaphone | 55 | Training bonus +40% for 3 turns |
| Empowering Megaphone | 70 | Training bonus +60% for 2 turns |
| Speed / Stamina / Power / Guts Ankle Weights | 50 each | +50% training bonus for that stat, +20% energy consumption (1 turn) — note: **no Wit version exists** |
| Good-Luck Charm | 40 | Training failure rate set to 0% for 1 turn |

#### Races
| Item | Cost | Effect |
|---|---|---|
| Artisan Cleat Hammer | 25 | Race bonus +20% (1 turn) |
| Master Cleat Hammer | 40 | Race bonus +35% (1 turn) |
| Glow Sticks | 15 | Race fan gain +50% (1 turn) |

### Rivals

- A random race may be marked with a **red/blue "VS" speech-bubble icon** and shimmer effect on the race button — this indicates a Rival race is available.
- **Winning (1st place)** vs. the rival grants **one random skill hint**, tied to either the race's distance or the running style you used.
- Scoring follows the same convention as Legend Races: placing 1st = win; placing better than the rival but not 1st = draw; placing worse = loss.

### Twinkle Star Climax (The Finale)

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

### Unique Skill Level-Ups

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

### Base Training Values (Facility Level 1, No Supports)

| Facility | Stat gains | Energy |
|---|---|---|
| Speed | +8 Speed, +4 Power, +2 SP | −19 |
| Stamina | +7 Stamina, +3 Guts, +2 SP | −17 |
| Power | +4 Stamina, +6 Power, +2 SP | −18 |
| Guts | +3 Speed, +3 Power, +6 Guts, +2 SP | −20 |
| Wisdom | +2 Speed, +6 Wisdom, +3 SP | +5 |

### Training Levels

- Same underlying rule as URA Finale: facilities start at Level 1 and **level up every 4 uses**, max Level 5.
- **New for Trackblazer:** Training Facility level can *also* be permanently raised by **1** via the Special Shop's Training Application items (150 coins each), stacking on top of natural use-based leveling — a mechanic URA Finale does not have.

### Character-Specific Events

- **Secret character events cannot trigger in Trackblazer** — this includes things like extra Triple Crown-specific rewards tied to a particular character. Confirms the same restriction uma.guide notes (e.g. Silence Suzuka's Runaway style).

### Scenario-Specific Epithets

- Achieving a Trackblazer-specific epithet triggers a special event with Chairman Akikawa Yayoi and grants bonuses. GameTora maintains the full epithet list on a separate page (gametora.com/umamusume/nicknames) rather than duplicating it on the scenario page itself — cross-reference the uma.guide Trackblazer document in this set for the detailed epithet/route tables.

### Scenario Factor (Inheritance)

- Trackblazer's scenario factor is called **"Climax Scenario"**, and grants **Stamina and Guts** bonuses on successful inheritance to a future trainee — this is the Legacy-passing bonus specific to this scenario, similar in concept to URA Finale's and Unity Cup's own scenario sparks.

### Stat Caps

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

### Quick-Reference Differences vs. URA Finale / Unity Cup

| Aspect | URA Finale | Unity Cup | Trackblazer |
|---|---|---|---|
| Progress goal | Scripted race-goal calendar | Scripted race-goal calendar + team races | Grade Point thresholds (self-paced racing) |
| Facility leveling | Repeat same stat 4x | Team stat rank | Repeat same stat 4x **+** shop items |
| Special currency | None | None | Shop Coins |
| Secret events | Enabled | Enabled | **Disabled** |
| New team/social mechanic | None | Unity Training / Spirit Bursts | None (solo, race-economy focus) |
| Scenario Link character | Aoi Kiryuin | (none — see Unity Cup file for Team Name characters) | None |

---

## Unity Cup (GameTora)

## Unity Cup Scenario — GameTora Reference Guide

**Server:** `[Global]`
**Status:** Active (authoritative Unity Cup reference post-2026-07-01 rework)
**Last Verified:** 2026-09-27 (metadata pass; source page last updated 2026-07-20)
**Supersedes:** `02-unity-cup.md` for mechanic numbers


*Source: gametora.com/umamusume/unity-cup — By Gertas, robflop & Burgh. Last updated 2026-07-20.*

> ⚠️ **This document reflects Unity Cup AFTER its major July 1, 2026 (Global) mechanics update.** If you've read an earlier Unity Cup guide (including the original overview document in this set, which predates this patch), several numbers below — especially Spirit Burst values and the existence of Extreme Spirit Bursts — are new or changed. This file supersedes those older figures; treat this as the current authoritative mechanical reference.

### Basic Information

- **Unity Cup** (JP/other servers: *Aoharu Hai*) originally released on Global **November 6, 2025** (August 30, 2021 on JP) as the **second permanent scenario**, after URA Finals.
- **Major update: July 1, 2026 (Global)** — corresponds to JP's January 20, 2023 update. This is the update this document is written against.
- You select Unity Cup from the main Career menu **before** choosing your trainee, and can freely switch between Unity Cup and URA Finals before every training session (they are not mutually exclusive unlocks).
- **Story framing:** the Unity Cup is a revived Tracen Academy team tradition; acting chairman for the scenario is **Riko Kashimoto**, standing in while Chairman Akikawa is away — this is why several Unity-Cup-specific mechanics (bond items, level-up conditions) don't involve Akikawa the way URA Finale/Trackblazer do.
- **New story characters:** in the finals, you face rival team **Zenith**, composed of original characters **Little Cocon** and **Bitter Glasse**, under Riko Kashimoto.

### July 1, 2026 Update — What Changed

This is the single most important thing to know if you've played Unity Cup before the patch or read older material:

- **Stat gains from Unity (Special) Training and Spirit Burst increased**, the **additional energy cost was removed** from Special Training, and **Unity Training frequency increased**.
- **New: Extreme Spirit Bursts** — a second, stronger burst available per team member after their normal Spirit Burst triggers (full details below).
- **Team Races now allow using Alarm Clocks on a loss** (previously not available at scenario launch).
- **Spirit Burst skill hints are no longer random** — they now pull from the specific support card's own hint pool (or an R-card pool / aptitude-based random hint for non-deck teammates).
- **New "Elite Teams"** can appear during the 4th Unity Cup team race under certain conditions, leading to a **strengthened Team Zenith** in the finals.
- **New Team Rank: S+** (above the previous cap of S).
- **Base training values were adjusted.**

If you're cross-referencing JP wikis, community spreadsheets, or older screenshots, assume they reflect **pre-update** numbers unless explicitly dated after January 20, 2023 (JP) / July 1, 2026 (Global).

### How Unity Cup Differs From URA Finals (Core Framing)

- Structurally, Unity Cup can be thought of as **"URA Finals plus a team layer"** — the base schedule, objectives, and even the final 3 races are essentially the same shape as URA Finale.
- The entirely new layer is **team racing**, functionally similar to the existing Team Trials PvP mode: once every 6 months, you run a 5-race Team Race set.
- **Training facility level is no longer tied to personal repetition** (as in URA Finale) — it's tied to your **overall team rank** for that stat instead.
- Trade-off vs. URA Finale: Unity Cup **takes slightly longer on average** to complete a career, which can make **URA Finale the better pick for pure inheritance/fan-farming runs**, while Unity Cup is generally the stronger choice for **building your best "aces."**

### Scenario Factor (Inheritance Spark)

- Unity Cup's scenario factor is simply called **"Unity Cup"** (アオハル杯シナリオ) and grants **Power and Wisdom** on successful inheritance — distinct from URA Finale's Speed/Stamina spark.

### Stat Caps (Unity Cup Scenario)

| Stat | Cap |
|---|---|
| Speed | 1300 |
| Stamina | 1300 |
| Power | 1300 |
| Guts | 1300 |
| Wisdom | 1800 |

(This matches the "future update" caps mentioned as pending in the original overview document — they are now confirmed active.)

### Base Training Values

**Global server (current):**

| Facility | Stat Gains | Energy |
|---|---|---|
| Speed | +8 Speed, +4 Power, +2 SP | −19 |
| Stamina | +7 Stamina, +3 Guts, +2 SP | −17 |
| Power | +4 Stamina, +6 Power, +2 SP | −18 |
| Guts | +3 Speed, +3 Power, +6 Guts, +2 SP | −20 |
| Wisdom | +2 Speed, +6 Wisdom, +3 SP | +5 |

**JP server (post their 2023 update — for reference/comparison only, NOT current Global values):**

| Facility | Stat gains | Energy |
|---|---|---|
| Speed | +8 Speed, +4 Power, +4 SP | −19 |
| Stamina | +8 Stamina, +6 Guts, +4 SP | −20 |
| Power | +4 Stamina, +9 Power, +4 SP | −20 |
| Guts | +3 Speed, +3 Power, +6 Guts, +4 SP | −20 |
| Wisdom | +2 Speed, +6 Wisdom, +5 SP | +5 |

Global has **not** yet received the JP-style SP bump shown in the second table — don't assume Global matches it.

### Unique Skill Level-Ups

Nearly identical thresholds to URA Finale:

| Milestone | Standard (Turf-leaning) | High-Dirt/Low-Turf (e.g. Haru Urara, Smart Falcon) |
|---|---|---|
| Valentine's Day (early Feb, Senior) | 60,000 fans | 40,000 fans |
| Early April (Senior) | 70,000 fans | 60,000 fans |
| Christmas / Late Dec (Senior) | 120,000 fans | 80,000 fans |

Difference from URA Finale: because **Chairman Akikawa isn't present** in this scenario, the **April level-up does NOT require any bond-gauge condition** (URA Finale ties a similar event to Director Akikawa's friendship bar).

### Gathering Team Members

Your team is built from:
- Your 6 **Support Cards** (excluding Pal-type cards),
- Story characters **Haru Urara, Rice Shower, Matikanefukukitaru, and Taiki Shuttle** (join relatively early),
- Additional **random characters** who join after each half-yearly Team Race.

⚠️ Only your actual **Support Cards** carry a bond gauge and can do Friendship (rainbow) Training — story/random teammates cannot, though they still fully participate in Special Training and Spirit Bursts.

### Unity Cup Team Races

- Occur **every 6 months**; a countdown to the next one is shown under the standard objective timer.
- Instead of facing other players, you choose one of **3 NPC teams** (strongest to weakest) — beating a stronger one raises your **league rank** further than beating a weaker one.
- The team-race menu lets you build sub-teams of 3 for **Short (Sprint), Mile, Medium, Long, and Dirt**, just like Team Trials.
- Each non-trainee teammate has their own Speed/Stamina/Power/Guts/Wisdom, ranked by their average — **raised only through Special Training**, and importantly, **NOT dependent on the support card's own level or limit break**.
- Before confirming an opponent, the game shows a circle-based estimate of your win odds per category — **aim for at least 3 circles total** as a safety margin, since losing **decreases your league rank**.
- **New in this update:** you can use an **Alarm Clock item to retry/undo a loss** in a Team Race (previously unavailable at scenario launch).
- Each Team Race ends with a results summary and **new random teammates joining** for future trainings/races.

### Special Training (Unity Training)

- Unlike URA Finale (only Support Cards + story characters can appear in training), **any team member can appear** in Unity Cup Special Training.
- A **white flame icon** on a teammate signals they're available for Special Training that turn.
- Training with a flagged teammate: raises their **Spirit gauge**, and grants **stat gains in all categories** with an extra bonus specifically matching the facility's specialty (e.g. Speed training gives bonus Speed **and** Power).
- **Your trainee only gains bonus stats/SP from Special Training if at least 2 white flames are present simultaneously** — 1 flame alone benefits only that teammate, not your trainee directly.
- **Scenario-linked support cards add +1 to every stat gained from Special Training** whenever they're part of that specific training instance.

#### Trainee Stat Gain Table (Special Training, current patch)

**Speed / Stamina / Power facilities:**
| # of flames | Primary stat | Secondary stat | Skill points |
|---|---|---|---|
| 2 | 2 | 0 | 1 |
| 3 | 4 | 1 | 3 |
| 4 | 6 | 3 | 5 |
| 5 | 10 | 5 | 7 |

**Guts facility:**
| # of flames | Guts | Speed | Power | Skill points |
|---|---|---|---|---|
| 2 | 2 | 0 | 0 | 1 |
| 3 | 4 | 1 | 1 | 3 |
| 4 | 6 | 2 | 2 | 5 |
| 5 | 10 | 3 | 3 | 7 |

**Wit facility:**
| # of flames | Wit | Speed | Skill points |
|---|---|---|---|
| 2 | 1 | 0 | 0 |
| 3 | 2 | 0 | 2 |
| 4 | 4 | 1 | 4 |
| 5 | 6 | 2 | 6 |

⚠️ **Important nuance on scenario-link bonuses:** Special Training and Spirit Burst stat gains are counted **separately** for the scenario-link +1 bonus. If a stat only comes from a Spirit Burst (not from the base Special Training tally), a scenario-linked card present won't boost that particular gain.

**Worked example from GameTora:** Yukino Bijin + Kitasan Black training Guts together = +2 Guts total (2 flames). Add Haru Urara (3rd flame) → normally +2 Guts/+1 Speed/+1 Power/+1 SP. But since Haru Urara is scenario-linked, each of those is bumped +1 further: final total = **+3 Guts, +2 Speed, +2 Power, +2 SP**.

### Spirit Burst

- Triggers automatically on the **next** Special Training with a teammate once their Spirit gauge is full.
- Grants: (1) a big stat boost to the teammate, (2) a moderate stat boost to your trainee, (3) a skill hint for your trainee — **no longer random**; see the hint-sourcing bullet directly below, which is the post-rework rule.
- **Skill hint sourcing (changed by the update):** now drawn from the specific support card's own hint pool (R-card pool substituted for non-deck teammates). If that pool is exhausted, falls back to a random hint based on your trainee's A-rank aptitudes.
- **Hint level formula:** `support card's hint bonus + 2` (so always at least Lv.2), **+1 more for scenario-linked cards.**
- You are **not** forced to trigger a burst the instant it's ready — you can hold it until the teammate is training the stat you actually want.
- **All bursts are purely additive** — no bonus for stacking multiple at once.
- A Spirit Burst on the **Wit** facility also grants **+5 extra energy recovery**.

#### Spirit Burst Values (current patch)
| Training type | Speed | Stamina | Power | Guts | Wit | Skill points |
|---|---|---|---|---|---|---|
| Speed | 15 | — | 7 | — | — | 5 |
| Stamina | — | 15 | — | 7 | — | 5 |
| Power | — | 7 | 15 | — | — | 5 |
| Guts | 3 | — | 3 | 15 | — | 5 |
| Wit | 2 | — | — | — | 15 | 5 |

**Scenario-linked bonus version:**
| Training type | Speed | Stamina | Power | Guts | Wit | Skill points |
|---|---|---|---|---|---|---|
| Speed | 20 | — | 10 | — | — | 10 |
| Stamina | — | 20 | — | 10 | — | 10 |
| Power | — | 10 | 20 | — | — | 10 |
| Guts | 5 | — | 5 | 20 | — | 10 |
| Wit | 5 | — | — | — | 20 | 10 |

### Extreme Spirit Burst (NEW in the July 2026 update)

- Becomes available for a teammate on their **next Unity Training after their normal Spirit Burst** triggers — i.e., a second, stronger burst per teammate per run.
- Does everything a normal burst does, **plus**:
  - Sets that training's **failure chance to 0%**.
  - **Raises the teammate's stat caps.**
  - Grants a hint for the matching **"Ignited Spirit"** skill (facility-specific).
- The Ignited Spirit hint starts at **Level 1**, scaling with the support card's Hint Lv. Bonus effect; if that skill's hint is already maxed or already owned, you get a random different Ignited skill hint instead.

#### Extreme Spirit Burst Values
| Training type | Speed | Stamina | Power | Guts | Wit | Skill points |
|---|---|---|---|---|---|---|
| Speed | 20 | — | 10 | — | — | 15 |
| Stamina | — | 20 | — | 10 | — | 15 |
| Power | — | 10 | 20 | — | — | 15 |
| Guts | 5 | — | 5 | 20 | — | 15 |
| Wit | 5 | — | — | — | 15 | 15 |

**Scenario-linked bonus version:**
| Training type | Speed | Stamina | Power | Guts | Wit | Skill points |
|---|---|---|---|---|---|---|
| Speed | 25 | — | 15 | — | — | 20 |
| Stamina | — | 25 | — | 15 | — | 20 |
| Power | — | 15 | 25 | — | — | 20 |
| Guts | 8 | — | 8 | 25 | — | 20 |
| Wit | 8 | — | — | — | 25 | 20 |

### Unity Cup Finals

- Occur after **4 Team Races**, facing Riko Kashimoto's **Team Zenith**.
- **Beating Team Zenith while achieving Rank S** makes Little Cocon and Bitter Glasse appear as **opponents in the URA Finale-style final race** later in the run.

### Training Facility Levels (Team-Rank Based)

| Team Rank | Training Facility Level |
|---|---|
| F/G | 1 |
| D/E | 2 |
| B/C | 3 |
| A | 4 |
| S | 5 |

Example: a team-wide Speed rank of A → Speed facility is Level 4, regardless of how many times you personally trained Speed.

### Unity Cup Special Skills

- **White-rarity versions** of scenario skills come from triggering **Extreme Spirit Bursts**.
- **Gold-rarity versions** (plus some additional white hints) come from the scripted **"Team Zenith Declares War"** event, occurring **Senior Year, late November**.
- The specific skill type awarded matches your **highest team stat rank**.
- Reward scaling is based on your **total count of Spirit Bursts + Extreme Spirit Bursts** across the career:

| Total Bursts | Reward |
|---|---|
| 4–6 | White hint Lv.1, +10 matching stat, +10 SP |
| 7–9 | White hint Lv.3, +20 matching stat, +20 SP |
| 10–12 | Gold hint Lv.1, +30 matching stat, +30 SP |
| 13+ | Gold hint Lv.3, +40 matching stat, +40 SP |

(GameTora notes exact values for very low burst counts, e.g. below 4, aren't confirmed.)

### Team Names

- Chosen in **Junior Year, second half of September**.
- The associated character **must be your trainee or one of your equipped Support Cards** — otherwise that team name option isn't selectable.
- Winning the Unity Cup Finals with a selected team name grants a **gold-rarity skill** tied to that name.

| Character | EN Team Name |
|---|---|
| Taiki Shuttle | Happy Hoppers |
| Matikanefukukitaru | Sunny Runners |
| Haru Urara | Carrot Pudding |
| Rice Shower | Blue Bloom |
| None of the above | Team Carrot |

(Matches the original overview document's table — this part of the scenario was **not** changed by the July 2026 update.)

### S and S+ Team Ranks

- Reaching overall **Team Rank S** auto-triggers an event granting a hint for the **"It's On!"**-type skill — **Level 1** normally, **Level 3** if training a scenario story character (e.g. Haru Urara).
- **New: Team Rank S+** — reaching it grants **another** hint for the same skill, meaning you can **max out its hint level** by hitting S+ specifically with a scenario-linked/story character.

#### Elite Teams ("Powerhouse Teams") — New Mechanic
Can appear during the **4th** Unity Cup Team Race if **all** of the following are true:
- Your league rank is **≥10**,
- Your team rank is **≥A**,
- You've triggered **at least one Extreme Spirit Burst**.

- Visually flagged with a **pink/purple background** and named after **Greek gods/goddesses**.
- **Winning** grants extra stat bonuses and unlocks a **strengthened Team Zenith** in the finals.
- Beating the strengthened Zenith grants **extra stats**, makes the **Unity Cup scenario factor easier to obtain**, and unlocks **"+" versions of the Unity Cup scenario skill factors** — flagged by **blue flames** instead of red on the pre-race screen.

### Pre-Update Reference (For Historical/JP-Comparison Purposes Only)

If you're reading older material dated before July 1, 2026 (Global) / January 20, 2023 (JP), these were the old values — **do not use these for current Global play**:

**Pre-update Special Training (extra energy cost applied, except Wit):**

*Speed/Stamina/Power:*
| # flames | Primary | Secondary | SP |
|---|---|---|---|
| 2 | 2 | 0 | 0 |
| 3 | 3 | 1 | 1 |
| 4 | 5 | 2 | 2 |
| 5 | 7 | 3 | 3 |

*Guts:*
| # flames | Guts | Speed | Power | SP |
|---|---|---|---|---|
| 2 | 2 | 0 | 0 | 0 |
| 3 | 2 | 1 | 1 | 1 |
| 4 | 4 | 2 | 1 | 2 |
| 5 | 6 | 2 | 2 | 3 |

*Wit:*
| # flames | Wit | Speed | SP |
|---|---|---|---|
| 2 | 1 | 0 | 0 |
| 3 | 2 | 0 | 1 |
| 4 | 3 | 1 | 2 |
| 5 | 4 | 2 | 3 |

**Pre-update Spirit Burst** (energy cost +5 except Wit; hints were fully random by A/S aptitude rather than card-pool based):
| Training type | Speed | Stamina | Power | Guts | Wit | SP |
|---|---|---|---|---|---|---|
| Speed | 15 | — | 7 | — | — | — |
| Stamina | — | 15 | — | 7 | — | — |
| Power | — | 7 | 15 | — | — | — |
| Guts | 3 | — | 3 | 15 | — | — |
| Wit | 2 | — | — | — | 10 | 5 |

**Pre-update Special Skills** (from Spirit Burst count alone, no Extreme Bursts existing yet):
- 4–9: white hint Lv.1
- 7–9: white hint Lv.3
- 10–12: gold hint Lv.1
- 13+: gold hint Lv.3

### Best Strategy Summary (Post-Update, GameTora-Informed)

1. **Since the energy penalty on Special Training is gone**, Wit-heavy "comfort" decks are less uniquely necessary than before the patch — you have more freedom to build around Speed/Power/Stamina without as much energy-management pressure.
2. **Prioritize teammates that are scenario-linked** (Haru Urara, Rice Shower, Matikanefukukitaru, Taiki Shuttle) for the Special Training/Spirit Burst stat-gain bonuses, and remember the bonus applies separately to Special Training vs. Spirit Burst gains.
3. **Don't trigger a Spirit Burst the instant it's ready** — hold it for a facility that matches your build's priority stat, especially now that its immediate follow-up (Extreme Spirit Burst) rewards being deliberate about *which* facility gets the extra attention.
4. **Push for Team Rank S+, not just S**, if you're chasing the max hint level on the "It's On!"-type skill, ideally with a scenario story character active.
5. **Track your league rank and Team Rank heading into the 4th Team Race** — reaching league rank 10+, Team Rank A+, with at least one Extreme Spirit Burst already triggered unlocks the harder-but-more-rewarding Elite Team / strengthened Zenith path.
6. **Total your Spirit Burst + Extreme Spirit Burst count across the run** and aim for 13+ if you want the best (gold, Lv.3) scenario skill reward.
7. **Use Alarm Clocks on a Team Race loss** now that the option exists, rather than accepting a league-rank setback outright.

---

