# Scenario Publisher References

**This is the consolidated reference for the owner-supplied publisher guides and mechanics extractions (uma.guide, GameTora, Game8, GameWith, Game8 JP, Kamigame). Do not create new files for this topic. Update this file instead.**

## Provenance

Embedded verbatim from `docs/scenarios/04-trackblazer-umaguide.md`, `docs/scenarios/05-trackblazer-gametora.md`, and `docs/scenarios/06-unity-cup-gametora.md`. Each original heading is demoted by one level under its source section. No content was summarised, deduplicated, or resolved; the three sources keep their own server/date qualifiers and any contradiction is recorded in the Disagreements section.

Two publisher extractions were added on 2026-10-03 (Round 7), promoted out of the gitignored root
`research-scratch/` and previously kept as standalone masters:

4. `scrape-game8-scenarios.md` (477 lines) as section "Scenario rules and phase structure (Game8, Global)".
5. `scrape-training-heuristics.md` (764 lines, 30-entry source ledger) as section "Training mechanics (GameWith, Game8 JP, Kamigame)".

Both are browser extractions held at arm's length, exactly like the three scenario guides: verbatim
numbers, the publisher and its last-updated date in the section header, and contradictions recorded
below rather than blended away. Neither file contains an em dash, so nothing was normalised.

6. `Our Grand Concert: the 2026-10-05 primary read`, added 2026-10-05 on the owner's instruction to research
the fourth `[Global]` scenario. Raw HTML from Game8 (607337 and 607687), GameTora and the rendered `[Global]`
news page 899, read to the end of the body and extracted with the page dates on file. This is the section
`docs/scenarios/07-grand-concert.md` and `docs/UMAMUSUME_REFERENCE.md` §2.2.4 both say they were missing, and it
is the source the 2026-09-27 owner suspension asked for. The repo-authored guide `07` is still not part of this
master's set: the guide now cites this section, which is the other direction.

## Disagreements

The three merged sources contain one live contradiction that the app carries as KI-15. It is documented here rather than resolved, because resolving it is a schema/owner decision (CONSTRAINTS escalation path 4/7), not a merge call:

- **Which Grade Point track a limited-range trainee uses.** The uma.guide Trackblazer file puts `Sprint Umas with poor aptitude in other distances'' on the **Dirt** requirement track. The GameTora Trackblazer file gives a turf character with poor aptitude outside short distances a **third** track in which only the Classic objective drops to 200. Neither names the aptitude letter that places a trainee in a track. `config/scenarios.php` carries all three tracks (`standard` / `dirt_leaning` / `limited_turf_range`); `TrainingRun::gradeObjectives()` renders only `standard` and says so, because a wrong denominator is worse than a conservative one. Tracked as **KI-15**, OPEN.
- **Stat-cap currency across sources.** The GameTora Trackblazer file (dated on the scenario launch day) predicts Global keeps flat 1200 caps; the later GameTora Unity Cup file confirms the post-2026-07-01 raised caps. This is a date conflict, not a merge conflict, and is governed by `docs/adr/0002` and `docs/adr/0003`; both statements are kept verbatim so the expiry is visible. See DESIGN-CORPUS D-228.
- **Server scope is mixed across the five sources.** The three scenario guides are `[Global]`. The Game8 scenario extraction is `[Global]`. The training-mechanics extraction is `[JP]`, taken from GameWith, Game8 JP and Kamigame, and its per-scenario tables are keyed to JP scenario names and JP print. Nothing in this master reconciles a JP number to a Global one, and no JP figure here may be quoted as a Global expectation. The split is recorded so a later reader does not read the training section as Global-validated; `docs/UMAMUSUME_REFERENCE.md` is where a JP-sourced mechanic earns its Global counterpart.
- **Publisher coverage is uneven across the three scenarios.** The uma.guide and GameTora guides cover Trackblazer and Unity Cup only. URA Finale enters this master solely through the Game8 extraction, so for URA Finale there is no second publisher to cross-check against, and for Trackblazer and Unity Cup the Game8 extraction is now the third voice rather than the only one.
- **Our Grand Concert: the two publishers disagree on nouns and agree on numbers.** Game8 prints the resource as "Performance Points" in five types (Dance, Passion, Vocals, Visuals, Composure); GameTora prints "performance tokens" (Dance, Passion, Vocal, Visual, Mental) and its own icon asset keys use `da`, `pa`, `vo`, `vi`, `me`. Game8 names both song bonus tiers "Mastery Bonus"; GameTora splits them into Practice Bonus (習得ボーナス) and Live Bonus (ライブボーナス), which are JP client strings. Neither site's head noun is the client's: notice 905 prints the resource as bare **Performance** in five types, **Dance, Passion, Vocals, Visuals, and Composure**. Game8's type list is therefore the client's and its "Points" is padding, while GameTora's "tokens" is padding and its fifth type "Mental" renders the JP word Global prints as **Composure**. Row 48 closed on official copy on 2026-10-05, which makes the two sites' disagreement evidence about the sites rather than about the game. What the two agree on is every cost figure, every effect pair, the five-stat cap line, the 23-song total, the four-and-one live structure and the five linked characters. Recorded per type in §7 rows 48 to 52. Rows 48 and 52 closed on `[Global]` notice 905 the same day, because an official page prints the client's own words for the resource, its five types and the title; rows 49, 50 and 51 stay open as guide divergences a capture would settle.
- **Our Grand Concert: song titles circulate in two languages.** Game8's Global guide prints English titles ("Believe in Miracles!"), GameTora prints romaji JP titles (Kiseki wo Shinjite!). That row-for-row correspondence was derived by matching effect text and cost figures row by row, and it lines up on all 23 rows. Whether the `[Global]` client localises the titles or prints the JP ones is unverified, and it is the question a single Lesson-menu capture closes.

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

## Scenario rules and phase structure (Game8, Global)

## Game8 Scenario Extraction (Global-relevant scenarios)

Source pages read with a real rendering browser (Playwright). All numbers below appear on the rendered page; nothing is reconstructed from memory or from another game.

Server note that applies to all three sections: each page carries a Global banner statement. The URA and Unity Cup pages both state that their scenario updates "were made on July 1, 2026 in the Global version". The Trackblazer page states the scenario is live Global as of March 12, 2026. JP-only facts appear only inside the per-page "Japan Scenario Release Dates" tables; those are tagged `[JP]` and are release history, not availability.

Scenario count check from all three pages (identical footer table "List of All Career Scenarios" on each page): URA Finale Scenario, Unity Cup Scenario, Trackblazer Scenario, Grand Concert (Grand Live) Scenario. That is the four-scenario Global list this extraction was scoped against.

---

### URA Finale (https://game8.co/games/Umamusume-Pretty-Derby/archives/536520, last updated July 6, 2026 06:18 AM)

Article title as printed: "URA Finale Scenario Guide". Page banner: "ⓘ Updates to the URA Finale scenario were made on July 1, 2026 in the Global version of Umamusume, ahead of its original anticipated release."

#### 1. Special rule this scenario adds

URA Finale is described as the base career: "the first career available in the Global version", "the most straightforward out of all" career modes. Its own resource layer is the rework added on July 1, 2026 in Global, which introduces a duel partner and a duel reward track that the pre-rework scenario did not have:

- Happy Meek appears on training after the new scenario event "I'm Here to Challenge You". She can appear on any stat. She does not give Friendship trainings, unlike Team Sirius (Passing the Dream On) and Heirs to the Throne (Esteemed and Adored). `[Global]`
- Dueling Happy Meek is the new action layer: you pick one of three options (two random stats plus the stat you dueled her on), each option carries an icon showing your chance of winning that option. `[Global]`
- Duel wins raise a "dueling level" with Happy Meek; at 6 duel wins she becomes a powered-up opponent in the URA Finals. `[Global]`
- Scenario Link character: Aoi Kiryuin "will occasionally appear during your career that will benefit your stats during her events with Happy Meek". `[Both]`
- Stat caps are scenario-specific and were raised by the rework (+200 to each of the five stats). `[Global]`

#### 2. Phase structure

- Class/year sequence in the fixed event calendar: Junior Year, Classic Year, Senior Year (three tabs, numbered 1, 2, 3). `[Both]`
- Junior: Inspiration at Start; "Road to Stardom" after 3 turns, pre-debut (Director Akikawa starts appearing in training); debut race "[Race] Junior Make Debut" after 11 turns; "After the Debut" gives Promotion to Beginner Class and unlocks running other races; "A Quirky Respondent?" Early July (last turn) puts Reporter Etsuko Otonashi into training. `[Both]`
- Classic and Senior each contain a 4-turn Summer Camp block (Early July, Late July, Early August, Late August). `[Both]`
- No extra race objective layer beyond the trainee's own race goals plus fan-count events; the final stage is the URA Finale three-race block. `[Both]`
- Final-stage condition chain, printed as three separate calendar rows with win-gated triggers: "After the URA Finale Qualifier" (trigger: Winning the URA Finale Qualifier), "After the URA Finale Semifinals" (Winning the URA Finale Semifinals), "After the URA Finale Finals" (Winning the URA Finale Finals). All three are marked "Affected by Race Bonus!". `[Both]`
- Career-end gates: "Individual Trainee Event" triggers on Winning the URA Finale Finals and gives the trainee's Good Ending. "A Super Successful Event!" triggers on Winning the URA Finale Finals plus Max Friendship with Director Akikawa. `[Both]`

#### 3. Numeric effects as printed

| Number as printed | Thing it modifies | Guide section | Tag |
|---|---|---|---|
| 1400 (Speed), 1400 (Stamina), 1400 (Power), 1400 (Guts), 1400 (Wit) | per-stat cap in this scenario after the July 1, 2026 rework | Increased Stat Caps | `[Global]` |
| +200 | the amount each of Speed, Stamina, Power, Guts, Wit caps were raised by | Increased Stat Caps | `[Global]` |
| 1200 | the "original base" cap for each stat | Increased Stat Caps | `[Global]` |
| gains "always halved" | training gains for any stat beyond 1200 | Increased Stat Caps | `[Global]` |
| 1200 or more | Power stat needed for the Fully Charged mechanic to pay off | Increased Stat Caps | `[Global]` |
| 30 Energy | what Director Akikawa gives on the third turn of summer camp | New Summer Event With Director Akikawa | `[Global]` |
| level 5 | every training facility for the whole summer camp duration | New Summer Event With Director Akikawa | `[Global]` |
| 30 skill points plus a Racing Spirit hint plus stat gains plus a stat cap increase | reward for winning a Happy Meek duel | Duel for Additional Stat Cap Increases and Skill Points | `[Global]` |
| 15 skill points plus "a small stat gain" | reward for losing a Happy Meek duel | Duel for Additional Stat Cap Increases and Skill Points | `[Global]` |
| 3 options | choices offered per duel (two random stats plus the dueled stat) | Duel for Additional Stat Cap Increases and Skill Points | `[Global]` |
| 6 wins | duel wins against Happy Meek needed to trigger powered-up Happy Meek | Powered Up Happy Meek | `[Global]` |
| 200+ skill points | end-of-run bonus for defeating powered-up Happy Meek | Powered Up Happy Meek | `[Global]` |
| 1 skill (Past My Limits) plus a chance at an enhanced Racing Spirit spark | end-of-run bonus set for defeating powered-up Happy Meek | Powered Up Happy Meek | `[Global]` |
| 20% | Training Stat Increase at Great mood | Improve Mood for Better Performance | `[Both]` |
| 4% | performance during race at Great mood | Improve Mood for Better Performance | `[Both]` |
| 4 times | repeats of the same stat training needed to upgrade that training level | Repeat Training 4 Times to Upgrade Training Level | `[Both]` |
| 4 turns each | length of each Summer training block | Ensure You Have Full Energy and Great Mood Before Summer | `[Both]` |
| Early July until Late August, Classic and Senior Years | when the summer blocks occur | Ensure You Have Full Energy and Great Mood Before Summer | `[Both]` |
| 60,000 Fans (Turf) or 40,000 (Dirt) | Valentine's Day, Early February Senior Year, requirement for Unique Skill Lvl Up | Run More Races to Get Fans | `[Both]` |
| 70,000 Fans (Turf) or 60,000 (Dirt), plus 3 Bar Friendship with Director Akikawa | Fan Fest, Early April Senior Year, requirement for Unique Skill Lvl Up and Mood Up | Run More Races to Get Fans | `[Both]` |
| 120,000 Fans (Turf) or 80,000 (Dirt) | Holiday Season, Late December Senior Year, requirement for Unique Skill Lvl Up | Run More Races to Get Fans | `[Both]` |
| 350-450 Sprint, 450-550 Mile, 600-700 Medium, 700-800 Long | recommended Stamina per distance | Ensure You Have Enough Stamina | `[Both]` |
| 1800 Wit, 1300 in everything else | the Unity Cup caps quoted while comparing scenarios | URA Finale or Unity Cup? | `[Global]` |
| 0% | failure rate that Enhanced/Extreme Spirit Bursts set in Unity Cup, quoted as a comparison point | URA Finale or Unity Cup? | `[Global]` |

Racing Spirit skills, printed with effects verbatim (all `[Global]`, earned by winning duels on the matching stat):
- Racing Spirit: Speed: "Slightly increase velocity upon approaching late-race."
- Racing Spirit: Stamina: "Moderately expend endurance to moderately increase velocity in the last spurt."
- Racing Spirit: Power: "Slightly increase velocity for a medium duration when trying to pass another runner in the last spurt."
- Racing Spirit: Guts: "Slightly increase acceleration when dueling."
- Racing Spirit: Wit: "Slightly increase velocity upon activating 2 skills during the second half of the race."
- Racing Spirit: Mood: "Stamina, Guts, and Wit might moderately increase when mood is good or great."
- Past My Limits (duel-track skill): "Increase velocity in the last spurt. Increase this skill's effectiveness based on the value of the user's highest attribute."
- Enhanced Racing Spirit spark: "gives a bonus to the specific stat it has as well as a hint for its Racing Spirit skill if the spark appears during inheritance."

#### 4. Training-relevant decision the scenario forces

- Training level is earned by repetition, not by team rank: "you can increase your training level by repeating the same stat training 4 times. Since the training level directly affects the amount of stat increase, repeating the same training is important in this career mode." `[Both]`
- Duel choice is the new forced decision: which of the 3 options to take (two random stats plus the dueled stat), read off the win-chance icon per option; the dueled stat determines which Racing Spirit hint you can earn ("duel on the stats you want a specific Racing Spirit for"). `[Global]`
- Recommended deck composition is by running style and distance, printed as 2 builds per distance group (all `[Global]`/`[Both]` as the guide's own builds):
  - Sprint Build 1: 4 Speed cards plus 2 Power cards. Sprint Build 2: 3 Speed, 1 Wit, 2 Power.
  - Mile Build 1: 4 Speed plus 2 Power. Mile Build 2: 3 Speed, 1 Wit, 2 Power.
  - Medium Build 1: 4 Speed, 1 Power, and 1 card slot whose stat type the page does not label. Medium Build 2: 3 Speed, 1 Wit, 1 Power, and 1 unlabeled slot.
  - Long Build 1: 4 Speed plus 2 unlabeled slots. Long Build 2: 3 Speed, 1 Wit, plus 2 unlabeled slots.
  - The unlabeled slots render as images whose alt attribute is the bare number "4215861" on the page; ❌ UNVERIFIED which stat type they are. Looked for alt text or a caption in the "Use Support Cards That Will Benefit Your Umamusume" table on 536520.
- Scenario-link card recommendation: "We recommend using Aoi Kiryuin (Trainer's Teamwork) if you do not have enough Support Cards yet as it boosts the effects of the events Aoi appears in." `[Global]`
- Inheritance decision: pick Legacy Umamusume that "provide boosts on stats that your Umamusume needs depending on what running style/distance they are proficient at"; Legacy skills also enter the purchasable skill pool. `[Both]`
- Race participation decision: run more races to hit the fan counts above, because fan tier affects final rating and events upgrade the Unique Skill at those thresholds. `[Both]`
- Scenario verdict printed by the guide: "the upgrades to the URA Finale scenario do not make it competitively comparable to Unity Cup or Trackblazer", but reaching good stats and skills is "much easier" now due to Happy Meek bonuses and the new caps; recommended specifically when you want the URA Finale spark on a Veteran. `[Global]`

#### 5. Event structure as presented

- Events are presented as a fixed calendar split into three year tabs (Junior, Classic, Senior), each row giving Event name, "Date and Requirements", and Information/Stat Boosts. `[Both]`
- Choice events show numbered physical positions (Top / Mid / Bottom) with the outcome of each printed:
  - New Year's Resolutions, Classic Year, Early January (Start of Turn): Top "Stat +10", Mid "Energy +20", Bottom "Skill Points +20". `[Both]`
  - New Year's Shrine Visit, Senior Year, Early January (Start of Turn): Top "Energy +30", Mid "All Stats +5", Bottom "Skill Points +35". `[Both]`
  - Raffle Time!, Senior Year, Early January (End of Turn): random among "All Stats Up", "Energy Up/Mood Up", "Energy Up only", "Mood Down". `[Both]`
- Random stat reward: Summer Camp (Year 2) Ends, Late August (End of Turn): "3 Random Stats + 5". Same at Summer Camp (Year 3) Ends. `[Both]`
- Fixed stat/skill-point payouts in the Senior block: At the Carrot Farm (Late December, 100,000 Fans): "Skill Points +30". A Three-Legged Race (Early November End of Turn, 50,000 fans): "Hint for Lvl 1 Iron Will", "Skill Points +20", "Wit +20". Going to the URA Finale! (Late December): "Skill Points +30". `[Both]`
- Post-race blocks: Qualifier win "All Stats +10, Skill Points +40"; Semifinal win "All Stats +10, Skill Points +60"; Finals win "All Stats +10, Skill Points +80"; all three "Affected by Race Bonus!". Twinkle Monthly Special Issue (after URA Finale races, Max Friendship with Etsuko Otonashi): "All Stats +5, Skill Points +20". A Super Successful Event! (win Finals plus Max Friendship with Director Akikawa): "Obtain Director's Fan, All Stats +15, Skill Points +50". Individual Trainee Event (win Finals): "All Stats +5, Skill Points +20". `[Both]`
- Inspiration events: three of them (Start, Early April Classic, Early April Senior), each listed as "Stat Boosts dependent on Legacy Uma". `[Both]`
- ❌ UNVERIFIED: the per-option win-chance percentages for the Happy Meek duel. The page shows an icon per option and states chances are indicated, but prints no numeric probability. Looked for it under "Duel for Additional Stat Cap Increases and Skill Points" on 536520.
- ❌ UNVERIFIED: numeric values of Aoi Kiryuin's scenario-link event bonuses on this page. The page says she "will benefit your stats during her events with Happy Meek" with no numbers. Looked for a bonus table on 536520 (there is none; the page links out to other articles, which I did not follow).
- ❌ UNVERIFIED: the per-turn count of Junior/Classic/Senior year on this page. Calendar rows use relative triggers ("After 3 Turns", "After 11 turns") and month labels, never a turns-per-year number. Looked in the "URA Finale Fixed Events Calendar" tables on 536520.
- ❌ UNVERIFIED: the stat-cap table's header row is image-only in one variant of the DOM; I recovered the five column names from the image alt text (Speed, Stamina, Power, Guts, Wit) and the body row reads 1400 five times. No separate pre-rework (1200) column is printed.

---

### Unity Cup (https://game8.co/games/Umamusume-Pretty-Derby/archives/545572, last updated July 7, 2026 02:39 AM)

Article title as printed: "Unity Cup Update Guide". Full scenario name as printed: "Unity Cup: Shine On, Team Spirit! (Aoharu Hai) is the updated second permanent scenario". Page banner: "ⓘ Updates to the Unity Cup were made on July 1, 2026 in the Global version of Umamusume: Pretty Derby, ahead of its original anticipated release in November 2026."

#### 1. Special rule this scenario adds

The scenario layer, in the guide's own terms, is team-based training instead of solo facility leveling:

- Teammates occupy training facilities. Training on a facility with a marked teammate raises that teammate's Spirit Burst Meter; when the meter is full, selecting training with them triggers a Spirit Burst. "A Spirit Burst will give your trainee extra stats from the facility she trains on. This applies not just to your trainee, but also to the team member who triggered the burst." You also get a random skill hint whenever a Spirit Burst is triggered. `[Both]`
- Unity Training: multiple team members in the same facility session each add a fixed stat bonus on top, and those bonuses stack with Spirit Burst values. `[Both]`
- Spirit Bursts are one per character: "Only 1 Spirit Burst will be available per character. Once triggered, they will not appear again." A blue flame indicator appears at the bottom-right of that character's icon afterward. `[Both]`
- Extreme Spirit Burst (ESB), added July 1, 2026: purple version of a Spirit Burst, gives "Ignited Spirit skills and significantly bigger stat boosts", can appear on a Support that already fired her regular burst, "There is a 0% Failure Rate in the training facility where the Support has active ESB", and after firing once it no longer repeats on that Support (purple flame marker). `[Global]`
- Team Races every six months are the scenario's extra race layer (five teams of five racers, similar to Team Trials). `[Both]`
- Training facility levels come from team stat rank rather than from using the facility. `[Both]`
- Opponent team ladder in round 4: Elite Team, then S+ Team Zenith, added July 1, 2026. `[Global]`
- Team Name selection with a skill reward, chosen in Junior Year Late September. `[Both]`
- Clocks may now be spent to redo a lost Unity Cup team race. `[Global]`

#### 2. Phase structure

- Years are the usual Junior / Classic / Senior. The scenario's own phase layer is five team-race rounds, printed as "Unity Cup Team Race Schedules": `[Both]`
  - Round 1: After Junior Year Late Dec
  - Round 2: After Classic Year Late June
  - Round 3: After Classic Year Late Dec
  - Round 4: After Senior Year Late June
  - Finals: After Senior Year Late Dec
- "Every six months in your career (Late June and Late December), you will have to participate in Team Races which has you fielding five teams of racers similar to Team Trials." Each team race is 5 races, one per distance (Sprint, Mile, Medium, Long, Dirt); clearing all five gives an increase to all stats sized by your performance. `[Both]`
- Round 4 is the phase where the extra opponent tier lives: Elite Team can appear, and beating it exposes S+ Team Zenith ("S+ Team Zenith can be encountered when you beat the Elite Team in the 4th round of the Unity Cup"). `[Global]`
- Final-stage condition: win against Team Zenith in the Unity Cup finals to receive your chosen Team Name's skill. Trainee race goals are unchanged and still govern run survival: "you will still need to do well in your trainee's race goals, including the final 3 URA Finale races. Similar to the URA Finale, failing your trainee's race goals enough times will lead to your career run ending." `[Both]`

#### 3. Numeric effects as printed

Caps and training:

| Number as printed | Thing it modifies | Guide section | Tag |
|---|---|---|---|
| 1300 (Speed), 1300 (Stamina), 1300 (Power), 1300 (Guts), 1800 (Wit) | per-stat cap after the July 1, 2026 update | The Stat Cap Has Increased | `[Global]` |
| +100 | cap raise applied to each of Speed, Stamina, Power, Guts | The Stat Cap Has Increased | `[Global]` |
| +600 | cap raise applied to Wit | The Stat Cap Has Increased | `[Global]` |
| 1200 | the "original base" cap; gains beyond it are "always halved" | The Stat Cap Has Increased | `[Global]` |
| 1200 or more | Power needed for the Fully Charged mechanic | The Stat Cap Has Increased | `[Global]` |
| 0% | failure rate in the facility holding an active Extreme Spirit Burst | Extreme Spirit Bursts Available | `[Global]` |

Unity Training stat bonus per participating Umas (rows are the facility you train on, columns are the bonus; printed as three tabbed tables, all `[Both]`):

| Umas in Unity Training | Facility trained | Bonuses as printed in that row |
|---|---|---|
| 2 Umas | Speed | Speed +2 |
| 2 Umas | Stamina | Stamina +2 |
| 2 Umas | Power | Power +2 |
| 2 Umas | Guts | Guts +2 |
| 2 Umas | Wit | Wit +1 |
| 3 Umas | Speed | Speed +3, Power +1, Skill Points +1 |
| 3 Umas | Stamina | Stamina +3, Guts +1, Skill Points +1 |
| 3 Umas | Power | Stamina +1, Power +3, Skill Points +1 |
| 3 Umas | Guts | Speed +1, Power +1, Guts +2, Skill Points +1 |
| 3 Umas | Wit | Wit +2, Skill Points +1 |
| 4 Umas | Speed | Speed +5, Power +2, Skill Points +2 |
| 4 Umas | Stamina | Stamina +5, Guts +2, Skill Points +2 |
| 4 Umas | Power | Stamina +2, Power +5, Skill Points +2 |
| 4 Umas | Guts | Speed +2, Power +1, Guts +4, Skill Points +2 |
| 4 Umas | Wit | Speed +1, Wit +3, Skill Points +2 |

Spirit Burst stat bonus (printed as tabbed tables "Spirit Burst (1 Uma)" and "Spirit Burst (2 Umas)" plus a scenario-link table, all `[Both]`):

| Burst condition | Facility trained | Bonuses as printed in that row |
|---|---|---|
| 1 Uma bursting | Speed | Speed +15, Power +7 |
| 1 Uma bursting | Stamina | Stamina +15, Guts +7 |
| 1 Uma bursting | Power | Stamina +7, Power +15 |
| 1 Uma bursting | Guts | Speed +3, Power +3, Guts +15 |
| 1 Uma bursting | Wit | Speed +2, Wit +10, Skill Points +5 |
| 2 Umas bursting | Speed | Speed +30, Power +14 |
| 2 Umas bursting | Stamina | Stamina +30, Guts +14 |
| 2 Umas bursting | Power | Stamina +14, Power +30 |
| 2 Umas bursting | Guts | Speed +6, Power +6, Guts +30 |
| 2 Umas bursting | Wit | Speed +4, Wit +20, Skill Points +10 |
| Scenario-link Support present | Speed | Speed +20, Power +10 |
| Scenario-link Support present | Stamina | Stamina +20, Guts +10 |
| Scenario-link Support present | Power | Stamina +10, Power +20 |
| Scenario-link Support present | Guts | Speed +5, Power +5, Wit +20 |
| Scenario-link Support present | Wit | Speed +5, Wit +15, Skill Points +5 |

Printed as-is note: in the scenario-link table the Guts row's 20 sits in the Wit column, with the Guts column blank in that row; the other two burst tables put their big row value in the matching column. Recorded exactly as rendered, not corrected.

Also printed: "Spirit Bursts are stacked in fixed values, so there is no multiplicative benefit to triggering multiple Spirit Bursts at the same time"; "2 or more Spirit Bursts stack additively"; "Spirit Bursts increase training energy consumption with the exception of Wit, which increases energy gain instead"; example: "+15 Stamina from a Spirit Burst of 1 Uma, the +2 from another Uma with Unity Training (total 2 Umas in training) will also apply". `[Both]`

Team rank and facility level:

| Number as printed | Thing it modifies | Guide section | Tag |
|---|---|---|---|
| G to F gives Level 1; E to D Level 2; C to B Level 3; A Level 4; S Level 5 | team stat rank mapped to that stat's training facility level ("if your team's overall Speed stat rating is A Rank, then your Speed training facility will be at Level 4") | Team Stat Level Affects Facility Level | `[Both]` |
| F +2 All Stats; E +2 All Stats; D +3 All Stats; C +3 All Stats; B +4 All Stats; A +5 All Stats; S +5 All Stats and +1 Hint Level It's On! | bonus granted on each increase of overall team rank (Team Power) | Bonuses When Raising Team Power | `[Both]` |
| +50 at rank 5 or above | the highest bonus to all attributes from your team rank when facing Team Zenith; "This makes it possible for a team with an A rating to beat them" | Aim For Rank 5 Against Team Zenith | `[Both]` |
| 10 or higher team rating, Overall Team rank A or higher, Extreme Spirit Burst activated at least once | the three Elite Team unlock conditions | Elite Team and S+ Team Zenith | `[Global]` |
| Rank 2 team, pink background | how Elite Team appears in the team selection menu | Elite Team and S+ Team Zenith | `[Global]` |
| around 20+ for each stat | stat boost for beating Elite Team in a Team Race | Elite Team and S+ Team Zenith | `[Global]` |
| around 28 for each stat | stat boost for beating S+ Team Zenith, plus a chance of a Unity Cup plus (+) spark | Elite Team and S+ Team Zenith | `[Global]` |
| 30 TP | cost of the end-of-run reroll used to chase those sparks | Unity Cup Plus Sparks | `[Global]` |
| 1 racer minimum, up to 3 racers per distance team | team composition rules for the five distance teams | Set Up Unity Cup Team Members | `[Both]` |

Spirit Burst skill rewards:

| Number as printed | Thing it modifies | Guide section | Tag |
|---|---|---|---|
| 13 or more bursts: Burning Spirit X Lv. 3 plus 20 Skill pts | reward tier | Exclusive Skills from Spirit Bursts | `[Both]` |
| 10-12 bursts: Burning Spirit X Lv. 1 plus 20 Skill pts | reward tier | Exclusive Skills from Spirit Bursts | `[Both]` |
| 7-9 bursts: Ignited Spirit X Lv. 3 plus 15 Skill pts | reward tier | Exclusive Skills from Spirit Bursts | `[Both]` |
| 4-6 bursts: Ignited Spirit X Lv. 1 plus 15 Skill pts | reward tier | Exclusive Skills from Spirit Bursts | `[Both]` |
| Senior Year Early November | when the burst-count skill is awarded | Exclusive Skills from Spirit Bursts | `[Both]` |

Printed burst-skill effects (all `[Both]`): Burning/Ignited Spirit SPD "Increases velocity mid-race. Scales according to the total Speed of team members."; STA "Recovers endurance mid-race. Scales according to the total Stamina of team members."; PWR "Increases acceleration late-race. Scales according to the total Power of team members."; GUTS "Increases velocity and acceleration late-race. Scales according to the total Guts of team members."; WIT "Improves navigation early-race. Scales according to the total Wit of team members." Note printed: "Rare versions of the skill have the \"Burning\" label. Common versions of them are referred to as \"Ignited\"."

It's On! layer:

| Number as printed | Thing it modifies | Guide section | Tag |
|---|---|---|---|
| It's On!, "Increase velocity when passing another runner mid-race." | the skill granted at Overall Team Rank S | It's On! Rewarded on Reaching Rank S | `[Both]` |
| +1 Hint Level at rank S; +3 Hint Levels with a Scenario-Link Trainee (Taiki Shuttle named) | hint level of It's On! from rank S | It's On! Rewarded on Reaching Rank S | `[Both]` |
| additional +1 Hint Level at Overall Team Rank S+; more added if the trainee is scenario-linked | extra It's On! hint level from the July 1, 2026 update | Extra It's On Rewarded on Team Rank S+ | `[Global]` |

Scenario-link bonuses (printed table, all `[Global]` as the guide's Global-facing content, `[Both]` for the mechanic itself):

| Character | Normal Bonus | Scenario Link Bonus |
|---|---|---|
| Taiki Shuttle | +10 Speed | +20 Speed and +10 Skill Points |
| Rice Shower | +10 Stamina | +20 Stamina and +10 Skill Points |
| Haru Urara | +10 Guts | +20 Guts and +10 Skill Points |
| Matikanefukukitaru | +10 Wit | +20 Wit and +10 Skill Points |
| Riko Kashimoto | +10 Wit and +10 Energy | +20 Wit, +10 Skill Points, +15 Energy and +1 Mood |

Team Name skills (chosen Junior Year Late September; reward requires beating Team Zenith in the finals; all `[Both]`):

| Team Name | Source character | Skill detail |
|---|---|---|
| Happy Hoppers | Taiki Shuttle | Mile Maven |
| Sunny Runners | Matikane Fukukitaru | Clairvoyance |
| Carrot Pudding | Haru Urara | Indomitable |
| Blue Bloom | Rice Shower | Cooldown |
| Team Carrot | None of the Above (default) | No Stopping Me! |

Printed note: "Team Sirius Group SSR is unusable in Unity Cup due to Rice Shower being associated with the scenario." Top suggested link skills as printed: Mile Maven for Mile races, No Stopping Me! for Pace/Late/End racers, Cooldown as a Long-distance recovery skill.

Result targets and dates:

| Number as printed | Thing it modifies | Guide section | Tag |
|---|---|---|---|
| 17,000+ rating Veterans | what the updated scenario lets you build | Unity Cup or Trackblazer Scenario? | `[Global]` |
| 2,000-2,500+ Skill Points | skill points obtainable, "Throne Group SSR considered", versus "Trackblazer's average of 2,800" | Unity Cup or Trackblazer Scenario? | `[Global]` |
| around 11-15 race wins | how few wins the updated scenario needs for its stat output | Unity Cup or Trackblazer Scenario? | `[Global]` |
| at most or close to 1300 | UG-grade stat lines reachable with the new caps | Unity Cup or Trackblazer Scenario? | `[Global]` |
| November 6, 2025 UTC (Half Anniversary Part 3, November 6 2025, 10 PM UTC) | Global release of the scenario | Release Date | `[Global]` |
| November 11, 2025 08:00 UTC, maintenance 02:00 UTC same day | Global balance patch, "introduces major changes from the JP 1st Anniversary several months early, such as the Guts rework, new race mechanics, better bad condition management, and mostly bufffed skills and events" | Balance Adjustments (Nov. 11, 2025) | `[Global]` |
| August 30, 2021, 6 Months | JP release date and JP duration of Aoharu Hai (アオハル杯) | Japan Scenario Release Dates | `[JP]` |

#### 4. Training-relevant decision the scenario forces

- The central trade is printed as: races are for teammates, not for you. "it is preferrable to maximize your teammate training (developing Spirit Bursts) over taking regular races unless these races contribute to your race goals, since taking a race means you'll be taking a turn off from developing your teammates' stats and rank, directly affecting also your progress of raising your Training Facility levels." `[Both]`
- Facility levels are earned by team stat rank rather than by repetition, explicitly contrasted with the other scenario: "Instead of leveling up with repeated use like in the URA Finale, training facilities will instead improve based on your team's overall ranking for that stat." `[Both]`
- Sequencing decision: "It is recommended to prioritize Unity Training early on to raise the stats and rank of your team member... Friendship Training bonds can come second, since Unity Training and Spirit Bursts do provide signifcant stat gains." `[Both]`
- Burst timing decision: "you do not need to immediately trigger a Spirit Burst. You can wait until the team member with a Spirit Burst is at the stat you want to increase before activating it." `[Both]`
- Team Zenith round-choice decision, printed with the option patterns: to guarantee Rank 5 "you must win the middle option once (except during Classic Year, Late December, or 1,1,2,1) and win the top option for all the other race periods"; "picking the middle option for the 1st race is the safest bet (2,1,1,1)"; if you won the top every time to reach the 4th race you can take the middle there "(1,1,1,2)", which also avoids "the risk of losing against the Turf Queens or The Apex". `[Both]`
- Recommended stat allocation guidance as printed: build Speed plus Wit (the all-around Sprint-Medium route), or carry 2-3 Wit cards; "you can adjust for just 1 Wit card for more Stamina and Guts options for longer distance builds". Deck composition decision printed: "you can still make results with the usual 4-Speed deck variation, but with the new mechanics and cards like Riko Kashimoto, you can get optimal results even with 3-Speed setups." `[Both]`
- Recommended decks by name, with slot counts as printed: `[Both]`
  - Speed Wit Deck: 3 Speed, 2 Wit, 1 Riko Kashimoto (usual go-to for Sprint and Mile); alternative 3 Speed, 3 Wit without Riko.
  - Front Runner Groundwork Deck: 3 Speed, 3 Wit variant plus 3 Speed, 2 Wit, 1 Power variant; recommended only for Front Runners who cannot inherit Groundwork consistently.
  - Double Pal Deck: 2, 2, 2 with a flexible slot (e.g. Rice Shower Power SSR); best mood handling early.
  - Speed Power Deck: 3 Speed, 2 Wit, 1 Riko; for Medium and possibly Long, Late/End styles.
  - Speed Stamina Deck: 3 Speed, 1 Riko, 2 Stamina / 4 Speed, 1 Riko, 1 Stamina / 3 Speed, 1 Riko, 1 Stamina, 1 Rice; for Long.
  - Throne Group Deck: 2, 2, 1, 1; either double Recreation (Pal/Throne) or Speed-Wit with Throne.
- Best card per type as printed in the recommended table: Pal Riko Kashimoto (Planned Perfection); Group Heirs to the Throne (Esteemed and Adored); Speed Kitasan Black (Fire at My Heels); Stamina Super Creek (Piece of Mind); Power Rice Shower (Happiness Just around the Bend); Guts Haru Urara (card name truncated in the page's alt text as "Haru Urara (Urara"); Wit Fine Motion (Wave of Gratitude). `[Both]`
- Recommended SSR Wit cards named: Fine Motion (Wave of Gratitude), Nice Nature (Daring to Dream), Mejiro Dober (My Thoughts, My Desires); SR alternatives Marvelous Sunday Wit SR, Matikane Fukukitaru Wit SR. `[Both]`
- Energy decision: Riko Kashimoto cards are "most notable for their energy management and training effects, helping mitigate increased energy consumption Unity Training and Spirit Bursts"; Tazuna Hayakawa still usable but Riko wins on being scenario-linked and her events give Stamina and Guts. Rice Shower Power SSR stands out for longer distances because it provides Swinging Maestro plus the scenario-link bonus Cooldown. `[Both]`

#### 5. Event structure as presented

- Mechanics are presented as a checklist block ("Unity Cup Tips, Mechanics, and Details") plus a "Quick Summary/Key Points" box with four bullets, then per-section tables. Large numbers are inside tables, and the multi-variant bonus tables sit behind tabs (2 Umas / 3 Umas / 4 Umas and Spirit Burst 1 Uma / 2 Umas), which I read from the hidden tab panels directly. `[Both]`
- Scenario-linked random events: "you may also get random events featuring the characters above while doing the Unity Cup. Having a character that is scenario-linked will improve the bonuses they give", with the numeric pair per character given above. `[Both]`
- Rank S award is an event: "Upon reaching Rank S for your overall team rank, you will get an event that gives you the skill It's On with a +1 Hint Level." `[Both]`
- Burst-count award is a dated event in Senior Year Early November, with the tier table above. `[Both]`
- Team Name selection is a dated choice event in Junior Year Late September; the offered names depend on which scenario-linked character is in your deck or as trainee, defaulting to Team Carrot. `[Both]`
- Team Info readout: "You can check how many Spirit Bursts you've done by clicking on the button beside your Team Rank. This will display your Team Info, which contains the total number of Unity Trainings and Spirit Bursts done so far." `[Both]`
- ❌ UNVERIFIED: exact stat gain per completed team race round. The page says only "you will receive an increase to all stats, with the amount based on your performance", with no number. Looked in "Train with Teammates to Win Team Races" on 545572.
- ❌ UNVERIFIED: numeric probability icons for the Elite Team / S+ Team Zenith matchups, and the exact stat line of the printed samples (the Elite Team Reward Sample, S+ Team Zenith Rewards Sample, and the "Sample Updated Unity Cup Scenario Result" are images; alt text is only "Elite-Team-Rewards", "S Plus Zenith Team Rewards", "Sample Unity 2 TM Opera O"). Looked in those blocks on 545572.
- ❌ UNVERIFIED: a per-round turn count for the team race rounds; the page anchors rounds to months ("After Junior Year Late Dec" and so on), never to a turn number. Looked in the "Unity Cup Team Race Schedules" table on 545572.

---

### Trackblazer (https://game8.co/games/Umamusume-Pretty-Derby/archives/580723, last updated August 25, 2026 02:16 AM)

Article title as printed: "Trackblazer (Make a New Track) Scenario Guide". Full name as printed: "Trackblazer: Start of the Climax (Make a New Track/MANT) is the third permanent scenario". Sibling guides named on the page (not read, per scope): Trackblazer Race Schedules, Trackblazer Items and Tier List, Trackblazer Epithets, All Trackblazer Events and Choices, How to Unlock Long TS Climax.

#### 1. Special rule this scenario adds

Two systems the base scenarios do not have, printed as the scenario's definition: `[Both]`

- Shop Coins and a Pro Shop. "Trackblazer lets you collect Shop Coins that you can use to buy items in the Pro Shop that will boost your performance within the current run of this career mode." Items "vary from improving your stats to giving you a certain condition, energy, support card bonds, and much more." `[Both]`
- Result Points replace race goals. "Unlike the URA Finale and Unity Cup, Trackblazer has no race goals and no Scenario Link. Every Umamusume will need to get enough Result Points by Late December of each year instead. Excess Result Points are not carried over to the following year." `[Both]`
- Rival races: flagged by the VS icon on the Race button, and only for distances at C aptitude or better; beating a Rival yields skill hints. `[Both]`
- Epithets/Titles as a cross-career achievement layer granting hints. `[Both]`
- Trainee secret events do not exist here: "Secret events of Umamusume don't trigger in the Trackblazer scenario... For example, Silence Suzuka cannot get the Runaway style in this career as it is tied to her secret event." `[Both]`
- The scenario's own summary line: "Trackblazer offers a more open career with no race goal restrictions, making the player focus their training strategy around Races and the Pro Shop to achieve the best results. This scenario does not have secret events." `[Both]`

#### 2. Phase structure

- Same Junior / Classic / Senior year frame, printed as three rotation columns; the guide's sample is a "Turf, Mile to Medium race rotation". `[Both]`
- No per-year turn quota is printed; instead the whole-career budget is stated: "Counting the trainee's debut but not the last turns for the Twinkle Star Climax, there are about 72 turns where the expected schedule consists of around 30 races or more. That's nearly half of all turns, and double or triple compared to previous scenarios, where 10-15 races are normally done." `[Both]`
- Extra objective layer: a Result Points checkpoint at Late December of each year, with year-end placement tiers, plus the Umamusume of the Year award on top of it. `[Both]`
- Rival appearances and Daily Sales shop access grow with racing: "You usually buy Alarm Clocks from the Daily Sales shop, which becomes available as you participate in daily races." `[Both]`
- Final-stage condition: "The last three races of the scenario are replaced by the Twinkle Star Climax. For these races, you need to earn enough Victory Points across all three to place first. This means it's possible to still get 1st overall even if you don't win all 3 races." `[Both]`

#### 3. Numeric effects as printed

| Number as printed | Thing it modifies | Guide section | Tag |
|---|---|---|---|
| Result Points by grade: G1 100, G2 80, G3 60, OP 40, Pre-OP 20 | Result Points earned per win; "you get more points by taking on harder ones. If you don't place first, you gain fewer points from the race" | Higher Grades Give More Result Points | `[Both]` |
| Year-end placement table, printed headers "Objective / Turf / Dirt": 1st 60 / 30; 2nd 300 / 200; 3rd 300 / 300 | printed directly under No Race Goals, where the prose is about reaching enough Result Points by Late December; the table itself gives no unit label for the numbers | No Race Goals | `[Both]` |
| Shop Coins by finish position: 1st 100, 2nd-3rd 60, 4th-5th 30, 6th and worse 0 | Shop Coins per race, "regardless of grade" | Earn Shop Coins and Buy Training Items | `[Both]` |
| 1st place only | the result needed against a Rival to earn its skill hint; "Placing higher than the Rival but not getting 1st place will be equal to a draw and will not give you a skill hint" | Face Rivals to Gain Skill Hints | `[Both]` |
| C aptitude or better | the only distances where Rivals appear at all | Face Rivals to Gain Skill Hints | `[Both]` |
| 50% minimum total Race Bonus | the deck threshold the guide recommends ("Aiming for a minimum of 50 Race Bonus from your Support Cards is recommended"); "At the minimum, that is achievable with at least 4 10% RB cards and 2 5% RB cards" | Gain More Stats Using Race Bonus / Support Effect Priorities | `[Both]` |
| at least 30 Races won | race-count target recommended per run | Gain More Stats Using Race Bonus | `[Both]` |
| about 72 turns, around 30 races or more (about 50% of turns) | the career's turn budget and how much racing consumes it | Race Scheduling and Rotations | `[Both]` |
| up to 5 Alarm Clocks per career | the reset allowance for a race you did not win in 1st; "Alarm Clocks are best used for maximizing your chances of getting 1st in G1 races" | The MANT Domino and Alarm Clocks | `[Both]` |
| zero (0) energy while racing, or 3 races in a row (higher chance at 4) | the triggers of Race Fatigue, which "has a chance of lowering your trainee's Mood, as well as applying the Skin Outbreak condition"; racing 4 in a row "can lead to some of your stats decreasing" | Manage Energy and Schedule to Avoid Race Fatigue | `[Both]` |
| Early July to Late August, in both Classic and Senior Years | the Summer Camp window, with "temporary instant access to Level 5 Training Facilities"; the training recommendation is to bank item and energy buffs for it | When to Train in Trackblazer | `[Both]` |
| Cleat Hammers do not stack: Master Cleat Hammer after Artisan Cleat Hammer gives "only the 35% from the Master Cleat Hammer", not "a 55% Race Stat gain" | race stat gain percentage from race-bonus items | Same Items Do Not Stack | `[Both]` |
| Empowering Megaphone: 60% Training stat gain for 2 turns, overwriting an earlier Coaching Megaphone | multi-turn item overwrite rule | Same Items Do Not Stack | `[Both]` |
| stat books at +7 or +15, avoid the +3 ones | recommended purchase tiers for stat scroll items | Prioritize Energy Recovery and Stats | `[Both]` |
| Royal Kale Juice: restores 100 Energy at the cost of 1 Mood | the energy item flagged as most prized; the mood debuff is fixed with a Cupcake | Prioritize Energy Recovery and Stats | `[Both]` |
| Grilled Carrots: increase the Friendship gauge of all support card characters by 5 | the early-career bond item; target "orange (80+)" friendship by the first Summer in early July | Use Some Items Immediately / Raise Bonds Early | `[Both]` |
| Scholar's Hat: base cost 280 Shop Coins, "the equivalent of 3 first-place wins" | the Fast Learner condition item, which "gives a discount when learning skills" | Buy the Fast Learner Item if Affordable | `[Both]` |
| three gold cleat hammers | the quantity the guide says to save for the Twinkle Star Climax, since cleat hammers raise race bonus only for the turn they are used | Save Gold Cleat Hammers for Twinkle Star Climax | `[Both]` |
| Unique Skill level-ups: Junior 15 Director Bond (1 Blue) and 5,000+ Fans; Classic 30 Director Bond (2 Blue) and 60,000+ Fans; Senior 50 Director Bond (Green) and 120,000+ Fans | the replacement Unique Skill progression, "these will only trigger during Late December of every year" | How to Increase Unique Skill Level | `[Both]` |
| Umamusume of the Year bonuses: Junior 15 Director Bond (1 Blue), 5,000+ Fans, 60 Result Points, 20 Reporter Bond; Classic 30 Director Bond (2 Blue), 60,000+ Fans, 450 Result Points, 40 Reporter Bond; Senior 50 Director Bond (Green), 120,000+ Fans, 480 Result Points, 60 Reporter Bond | the extra stats and Glittering Star skill hints for winning the year-end award | Paired Stats and Skill Points Bonuses for Umamusume of the Year | `[Both]` |
| Sparks needed to reach A from a base grade: B 1, C 4, D 7, E 10 | pink inheritance sparks spent on an aptitude | Raise Aptitudes to Cover a Lot of Races | `[Both]` |
| up to 4 grades, with 10-star sparks | the aptitude raise allowed at initial trainee selection | Raise Aptitudes to Cover a Lot of Races | `[Both]` |
| 9 pink spark types, totaling 18 stars | the inheritance ceiling per Umamusume | Raise Aptitudes to Cover a Lot of Races | `[Both]` |
| 2 Wit cards in all decks, 2 Speed cards in most regular decks | deck composition advice to "easily hit the cap while training Power" | Best Support Cards for Trackblazer | `[Both]` |
| 4 Guts and 2 Wit (optionally 3 Guts, 2 Wit, 1 Speed) | the Guts build template; the page states Guts is "the only facility that raises 3 stats at once", one of the three being Speed | Best Support Cards for Trackblazer | `[Both]` |
| deck samples: 75, 65, 70, 70, 60, 65, 55 total Race Bonus, with Sparks listed as Stamina/Power or Power | the printed Race Bonus totals of the seven example decks | Support Card Setups | `[Both]` |
| Matikanefukukitaru (Touching Sleeves Is Good Luck! ♪): +30 to all stats at max level, potentially +21 additional all stats, plus 10% Race Bonus | the recommended borrow card | Recommended Support Cards | `[Both]` |
| Narita Top Road (Peachy Silhouette): 20% Training Effectiveness at 200,000 Fans, plus 10% Race Bonus | the Fan Bonus scaling card | Recommended Support Cards | `[Both]` |
| Biko Pegasus (Double Carrot Punch!): unconditional 10% Race Bonus and 20% Training Effectiveness | general Speed card | Recommended Support Cards | `[Both]` |
| Kitasan Black (Fire at My Heels): 5% Race Bonus | still rated a top Speed card via Professor of Curvature | Recommended Support Cards | `[Both]` |
| Race Bonus values per card as printed: Marvelous Sunday (Dazzling Day in the Snow) 15% at max level; Nishino Flower (Lifting Your Spirits) 15% ("One of two SR cards with 15% Race Bonus"); Admire Vega (Aim for the Brightest) 15% ("The only other SR support with 15% Race Bonus"); Nice Nature (Daring to Dream) 15% ("The only Wit card with 15% Race Bonus"); Yaeno Muteki (Fiery Discipline) 10% with Power Bonus; Shinko Windy (///WARNING GATE///) 10%; El Condor Pasa (Mud-Caked Compañero) 10%; Super Creek (Piece of Mind) 10%; Special Week (The Setting Sun and Rising Stars) 10%; El Condor Pasa (Champion's Passion) 10%; Haru Urara (Urara's Day Off!) 10%; Fine Motion (Wave of Gratitude) 10%; Mejiro Dober (My Thoughts, My Desires) 10%; Agnes Tachyon (Experimental Studies on Subject A) 10% ("One of 2 Wit SRs currently with 10% Race Bonus"); Marvelous Sunday (A Marvelous ☆ Plan) 10% ("The other Wit SR with 10% Race Bonus"); Heirs to the Throne (Esteemed and Adored) 10%; Kitasan Black (Fire at My Heels) 5%; Ines Fujin (Watch My Star Fly!) 5% with 15% Training Effectiveness and Speed Bonus; Mr. C.B. (Dear Mr. C.B.) 5% | the Race Bonus values printed per recommended card, used to reach the 50% deck total | Recommended Support Cards | `[Both]` |
| Priority ranking as printed: Race Bonus ★★★, Initial Stat Bonuses ★★, Initial Friendship ★ | which support effects to chase | Support Effect Priorities | `[Both]` |
| 30 minutes to an hour per run | the time cost of a Trackblazer career, because item and schedule management replaces rest and recreation | Races and Planning Take Time | `[Both]` |
| March 12, 2026, 10PM (UTC) | Global release of the scenario, "Now Available as of March 12, 2026" | Release Date | `[Global]` |
| February 24, 2022 | JP release of クライマックスシナリオ (Trackblazer MANT) | Japan Scenario Release Dates | `[JP]` |
| August 30, 2021 (Unity Cup Aoharu Hai) and August 24th, 2022 (Grand Live) | the JP previous/next scenario dates printed around Trackblazer | Japan Scenario Release Dates | `[JP]` |

Scenario spark table as printed (stats granted by the scenario spark of each career): `[Both]`

| Scenario | Stats |
|---|---|
| Grand Concert | Speed + Guts |
| Trackblazer | Stamina + Guts |
| Unity Cup | Power + Wit |
| URA Finale | Speed + Stamina |

Printed line on the Trackblazer spark: "a spark based on the scenario, called TS Climax Scenario, can be gained upon clearing it. This spark boosts Stamina and Guts stats if it activates during inspiration."

Title/Epithet rewards as printed: `[Both]`

| Title | Conditions | Reward |
|---|---|---|
| Legendary | Obtain the Spring Champion and Fall Champion titles, and either the Stunning or Lady titles | Homestretch Haste hint +1 |
| Mile a Minute | Win the following Mile races: Oka Sho, NHK Mile Cup, Yasuda Kinen, Victoria Mile, Mile Championship, Hanshin Juvenile Fillies; Asahi Hai Futurity Stakes may substitute | Mile Straightaway ◯ hint +1 |
| Dirt G1 Dominator | Win 9 G1 Dirt races | Top Pick hint +1 |

Scenario skill: Radiant Star (rare) and its base Glittering Star. "Radiant Star is a rare skill that increases velocity & acceleration, and provides recovery at a random point in the second half of the race. Its effects get stronger the more races you win. However, due to its high cost, we only recommend learning this skill if you have spare skill points. The Radiant Star skill is obtained by winning the Twinkle Star Climax." `[Both]`

#### 4. Training-relevant decision the scenario forces

- The scenario inverts the usual priority: "Unlike the URA Finale where training itself is important and the Unity Cup where Unity Training is the highest priority, winning Races is the key to developing a trainee into a strong, competitive Umamusume the Trackblazer Scenario." `[Both]`
- Training level is earned the base way: "Leveling up facilities in Trackblazer works just like the URA Finale, where they increase in level the more you use them. As such, it's recommended to have at least 2 of the types you want in your deck to stack Friendship training." `[Both]`
- Training Applications: "special items that raise a training facility's level and can also be bought with coins. Only buy these if you can afford them, since most stat gains from training are expected during the Summer, where all facilities are fixed at Level 5." `[Both]`
- Skip-a-race decision, printed with its condition: "you can also choose to forego racing in a G2 or G3 race if you have Friendship Training on a stat that you want to improve (like Speed or Wit). Although you will lose out on Shop Coins, the stats you get from training can outweigh what you can get from winning the race. This is especially true if your scheduled G2 or G3 race does not have a Rival in it." `[Both]`
- The failure spiral is the reason item stock matters: "Lose Race → Less Shop Currency (→ Need More Currency? Do More Races) → Less Items Over Time → Less Training with Items → Less Overall Trainee Progression." `[Both]`
- Recommended allocation is stated through deck archetypes rather than target stat values: 2 Wit in every deck, 2 Speed in most regular decks, Race Bonus at 50%+ minimum, win 30+ races, and the three support effects ranked as printed (Race Bonus ★★★, Initial Stat Bonuses ★★, Initial Friendship ★). `[Both]`
- Named recommended stat caps/skirts: raise "at least" the Mile-Medium-Long, Mile-Medium, or Medium-Long Turf aptitude coverage to A, so Rival races can appear; "Having B-aptitude can be acceptable, but A is the optimal grade for a higher chance of winning." `[Both]`
- Wide-aptitude trainees favored as printed: Agnes Digital (Full-Color Fangirling), Taiki Shuttle (Wild Frontier), Oguri Cap (Starlight Beat), El Condor Pasa (El Numero 1). Narrow-pool trainees harder here: Manhattan Cafe (Creeping Shadow), Meisho Doto named in prose; Smart Falcon (LOVE☆4EVER) listed as Dirt-specialized and disadvantaged because "Dirt races at distances of Mile and above are rare in Junior Year". `[Both]`
- F2P note as printed: "The most important stat in Trackblazer is Race Bonus, and building a strong deck using free cards is fairly easy." `[Both]`

#### 5. Event structure as presented

- Mechanics are presented as an "Explained" checklist (No Race Goals / Earn Shop Coins and Buy Training Items / Face Rivals to Gain Skill Hints / Secret Events Don't Trigger / Twinkle Star Climax Replaces URA Finals) plus a Quick Summary box, then strategy checklists and item tier tables. `[Both]`
- Random scenario events are two-option only: "During the scenario, you may encounter random events that give you two choices. The top choice will always provide a stat or mood boost while the bottom option will give you a fixed skill hint. There doesn't seem to be a way to reliably trigger these, so you shouldn't rely too much on the random events for skills." `[Both]`
- Annual checkpoints are Late December events (Unique Skill level-ups, Umamusume of the Year) rather than the usual calendar events. `[Both]`
- Most trainee events are absent: "Nearly all other events, such as character-specific events and annual events (New Year Shrine Visits, Raffle Ticket), are non-existent." Positive side as printed: no negative events or tough goal races, so Gold City and King Halo careers ease, and Narita Taishin never faces her mood-drop event Don't Say It!, and Haru Urara or Fuji Kiseki are not forced into the Arima Kinen. Negative side: Silence Suzuka cannot obtain event skills (locked out of Runaway), Mayano Top Gun cannot trigger the event giving Straightaway Spurt or Head-On. `[Both]`
- ❌ UNVERIFIED: the unit of the "Objective / Turf / Dirt" table (rows 1st, 2nd, 3rd with 60/30, 300/200, 300/300). The page prints no header or caption naming what those numbers measure; the surrounding prose is about Result Points at Late December. Looked in the "No Race Goals" block on 580723, including the table's HTML and its preceding elements (only the H3 heading and an empty div are there).
- ❌ UNVERIFIED: item prices and per-item stat values for the Pro Shop tier list. The tier table cells are item icons under category labels (Immediate Use, Immediate Use only if affordable, Save for Training, Save for Races, Cure Conditions, Fan Farming Only best for fan-heavy G1s and TS Climax races, Inefficient) with no numbers; the guide defers detail to the Trackblazer Items Guide, which is a different URL and out of scope. Looked in "Pro Shop Item Strategy" on 580723.
- ❌ UNVERIFIED: the Victory Point threshold needed to place first in the Twinkle Star Climax. The page states the requirement qualitatively ("enough Victory Points across all three") and names the item strategy for it, with no number. Looked in "Twinkle Star Climax Replaces URA Finals" on 580723.
- ❌ UNVERIFIED: how many races appear in the printed Junior/Classic/Senior rotation samples. The three rotation cells are images (alt text "Turf Mile Medium Race Junior", "Turf Mile Medium Race Classic", "Turf Mile Medium Race Senior") with no readable list. Looked in "Race Scheduling and Rotations" on 580723.
- ❌ UNVERIFIED: numeric effect values for Radiant Star and Glittering Star. The page gives the effect text only, no percentage or duration. Looked in "Radiant Star and Glittering Star" on 580723.

---

### Terminology Seen

| English string as printed | what it refers to |
|---|---|
| URA Finale Scenario, URA Finale Qualifier, URA Finale Semifinals, URA Finale Finals | the base career and its three final races; the qualifier/semifinal/final rows are the win-gated calendar events |
| Unity Cup: Shine On, Team Spirit! (Aoharu Hai) | the team-race scenario's Global name, with the JP name in parentheses |
| Trackblazer: Start of the Climax (Make a New Track/MANT) | the third permanent scenario's Global name plus JP aliases |
| Grand Concert (Grand Live) Scenario | the fourth Global scenario, listed in the shared scenario footer table |
| Happy Meek | the URA rework's duel partner who appears on training |
| Aoi Kiryuin, Director Akikawa, Reporter Etsuko Otonashi, Riko Kashimoto, Tazuna Hayakawa | named characters tied to scenario events and training appearances |
| Scenario Link, scenario-linked Support, Scenario Link Bonus | the character-pairing bonus layer that upgrades event values and sparks |
| I'm Here to Challenge You | the new URA scenario event that switches on Happy Meek training appearances |
| Racing Spirit: Speed, Racing Spirit: Stamina, Racing Spirit: Power, Racing Spirit: Guts, Racing Spirit: Wit, Racing Spirit: Mood | the six duel-earned hint skills in the URA rework |
| Past My Limits | the URA duel-track skill awarded for beating powered-up Happy Meek |
| dueling level | the counter that rises with each Happy Meek duel win |
| enhanced version of the Racing Spirit spark | the URA spark upgrade that adds a stat bonus and a Racing Spirit hint on inheritance |
| Unity Training | the shared facility training with teammates that carries the per-Uma stat bonuses |
| Spirit Burst, Spirit Burst Meter, Extreme Spirit Bursts (ESB) | the meter-driven burst, its fill track, and the July 1, 2026 purple tier |
| Ignited Spirit SPD / STA / PWR / GUTS / WIT and Burning Spirit SPD / STA / PWR / GUTS / WIT | the burst-count exclusive skills, common and rare tiers |
| Unity Cup plus (+) sparks, Ignited Spirit: Speed + | the enhanced spark tier from S+ Team Zenith |
| Team Zenith, Elite Team, S+ Team Zenith | the round-4 and finals opponent tiers (Elite Team is named after Greek figures, e.g. Team Aeon, Team Hephaestus) |
| Team Power, Overall Team rank, Team rating | the aggregate team progression whose rank drives facility levels and rewards |
| Team Trials | the existing mode the Team Race lineup is compared to |
| Turf Queens, The Apex | named opponent teams to avoid before your rank is high enough |
| Team Carrot, Happy Hoppers, Sunny Runners, Carrot Pudding, Blue Bloom | the Unity Cup team name options and their skill rewards |
| Result Points | the Trackblazer currency earned per race, reset at each Late December checkpoint |
| Victory Points | the currency of the three Twinkle Star Climax races |
| Twinkle Star Climax, TS Climax, Long TS Climax | the scenario's replacement of the final three races; Long TS Climax has its own guide link |
| TS Climax Scenario | the name of the Trackblazer scenario spark |
| Shop Coins, Pro Shop, Daily Sales | the in-run currency, the shop, and the rotating offer list that unlocks Alarm Clocks |
| MANT Domino | the guide's name for the loss spiral from race defeats to item income to progression |
| Alarm Clock | the item that re-runs a race you failed to win, up to 5 per career |
| Radiant Star, Glittering Star | the Trackblazer-exclusive rare skill and its base version |
| Epithet, Titles, Legendary, Mile a Minute, Dirt G1 Dominator, Spring Champion, Fall Champion, Stunning, Lady | the Trackblazer achievement layers and their names |
| Director Bond, Reporter Bond | the two bond tracks that gate Unique Skill level-ups here |
| Umamusume of the Year | the Late December year-end award that adds stats and Glittering Star hints |
| Race Fatigue, Skin Outbreak | the over-racing penalty and the condition it can apply |
| Race Bonus (RB), Fan Bonus, Training Effectiveness, Initial Stats, Initial Friendship | support card effects ranked by the Trackblazer guide |
| Training Application, Cleat Hammer (Artisan, Master, Gold), Megaphone (Coaching, Empowering), Ankle Weight, Whistle, Good-Luck Charm, Grilled Carrot, Cupcake, Miracle Cure, Rich Hand Cream, Vita, Royal Kale Juice, Scholar's Hat | Pro Shop item names as printed |
| Fast Learner | the condition from Scholar's Hat that discounts skill learning |
| Friendships gauge "orange (80+)" | the friendship threshold the guide aims at before the first summer |
| sparks, pink sparks, 10-star sparks, inheritance, Legacy Umamusume | the pre-career aptitude and stat inheritance system |
| Stat Cap, Stat Cap increase, "values that will be added will always be indicated in gold text" | the cap-raising layer shared by the July 1, 2026 updates |
| Fully Charged | the mechanic the guides cite as the reason to push Power past 1200 |
| UG rank, UG-grade stat lines, Class 6, TP | rating and currency terms used in the result targets and reroll cost |
| Groundwork, Iron Will, It's On!, No Stopping Me!, Mile Maven, Clairvoyance, Cooldown, Indomitable, Professor of Curvature, Swinging Maestro, Gourmand, Fast and Furious, Killer Tunes, Restless, Speed Star, On Your Left!, Daring Strike, The Bigger Picture, Straightaway Spurt, Head-On, Runaway, Homestretch Haste, Mile Straightaway ◯, Top Pick, Ramp Up, Tail Held High, Taking the Lead, Don't Say It! | skill and event names the scenario pages reference as rewards or locks |
| Champions Meetings, Team Trials, Racing Carnival | the endgame modes the guide cites as the reason to exceed caps |

---

### Read Failures

No page failed to render. All three URLs returned their article and were read to the end.

- All three pages were loaded and read. Final reads: URA 536520 (15,485 characters of article text, full), Unity Cup 545572 (28,208 characters, full), Trackblazer 580723 (30,786 characters, full).
- Shared-browser contention: another agent repeatedly navigated the same Playwright tab mid-read (observed landings: gamewith.jp/uma-musume/article/show/409161, game8.jp/umamusume/372572, game8.jp/umamusume/454202, game8.jp/umamusume/372297, gamewith.jp/uma-musume/article/show/353546, and one page that reported "Loading https://game8.jp/umamusume/372572" while still on my URL). Resolved by navigating and reading inside one atomic call per chunk and by asserting the returned `location.href` matched my target URL on every read. No chunk was accepted from a page whose URL did not match.
- One call to `browser_run_code_unsafe` returned "MCP error -32603: MCP tool invocation did not complete" while inspecting the Trackblazer placement table. It was retried with a smaller extraction and succeeded; the result is the "Objective / Turf / Dirt" table HTML quoted above.
- One early attempt at the generic `browser_evaluate` recipe in the brief hit Game8's "free member" modal (`.article-body` matched a dialog, returning 1,411 characters of signup text). The article container in this build is `.p-archiveContent__main`; that selector was used for all reads.
- A blind "click every Show more control" pass was tried once and navigated the tab to the site's `/ranking` page instead of expanding the article. It was abandoned. No content on any of the three pages was behind a Show more control: a computed-style sweep of `.p-archiveContent__main` found no collapsed nodes on the URA page, and the Unity Cup page's only collapsed nodes were three `.a-tabPanel` tables (2 Umas, 3 Umas, 4 Umas and the two Spirit Burst variants), which I read directly from the hidden panels' DOM rather than by clicking. Trackblazer has zero `.a-tabPanel` nodes.
- Table headers on the URA stat cap table, the Unity Cup stat cap table, and the Unity Cup facility-level table are images. Column and row names were recovered from the images' `alt` attributes (Speed, Stamina, Power, Guts, Wit; G/F, E/D, C/B, A, S). Where an icon had no alt text (the Medium and Long deck rows on the URA page, one cell reading `4215861`), the gap is flagged in that section rather than guessed.
- One image alt string is truncated in the page itself: "Haru Urara (Urara" in the recommended support card table.
- Off-page links were deliberately not followed (Stat Cap Increase Guide, Friendship Training Guide, How to Win Unity Cup Team Races, Trackblazer Race Schedules, Trackblazer Items and Tier List, Trackblazer Epithets, All Trackblazer Events and Choices, How to Increase Unique Skill Level, Legacy and Sparks Guide). Anything only available there is recorded as ❌ UNVERIFIED in the relevant section.
- Ad, affiliate, comment, and "Author" blocks were ignored. One Unity Cup page comment contains vocabulary outside the lore gate; it is user text, not guide content, and is not reproduced here.

---

### Disagreements

1. Scope of the 0% failure rate from a purple Spirit Burst. The URA page (July 6, 2026) writes that Unity Cup's version "set[s] failure rate to 0% whenever it appears on any training", while the Unity Cup page (July 7, 2026) writes "There is a 0% Failure Rate in the training facility where the Support has active ESB". One facility versus any training is a real difference in play. Both statements listed; no resolution attempted.
2. Name of that same mechanic. The URA page (July 6, 2026) calls it "Enhanced Spirit Bursts"; the Unity Cup page (July 7, 2026) calls it "Extreme Spirit Bursts (ESB)" and is the page that renders the in-game tooltip wording. Recorded as two published English strings for one July 1, 2026 mechanic; I did not decide which is the client term.
3. Skill-point output per scenario. The Unity Cup page (July 7, 2026) states the updated Unity Cup yields "2,000-2,500+ Skill Points" against "Trackblazer's average of 2,800". The Trackblazer page (August 25, 2026) prints no skill-point average at all, so the comparison has only one side published. Flagged rather than reconciled.
4. Stat cap figures and the export. The pages print 1400 per stat for URA Finale, 1300 per stat plus 1800 Wit for Unity Cup, and no cap figure for Trackblazer, all described as post-July 1, 2026 scenario caps with a 1200 base. These are the guide-stated scenario cap values and are not the same quantity as the `hard_caps` arrays in your export, which I did not have on any page. I am not claiming the two agree or disagree; I am flagging that the numbers I read cannot be matched one-to-one against that field without a definition of what each measures.
5. Scenario ordering, minor. The Unity Cup page's "Next" cell dates Trackblazer (MANT) at February 24, 2022 in JP, and its own Japan Scenario Release Dates block lists Aoharu Hai at August 30, 2021 with a "(6 Months)" duration. The Trackblazer page repeats both. No conflict found on dates; noted only because the "(6 Months)" label appears on one page and not the other with no explanation of what it measures.

---

## Training mechanics (GameWith, Game8 JP, Kamigame)

## Umamusume: Pretty Derby [JP] Training Mechanics: Raw Extraction

Scope: mechanical rules extracted from JP tier-A strategy wikis (GameWith, Game8 JP, Kamigame) using a live
rendering browser session (Playwright MCP). All values are transcribed exactly as printed on the page.

Method note, because it affected reliability: a concurrent agent in this workspace was driving the same single
browser tab, and it re-targeted the tab between my `browser_navigate` and `browser_evaluate` calls for a large share
of attempts. Plain navigate-then-read worked only intermittently. What worked reliably was issuing
`browser_navigate` and `browser_evaluate` in the same turn, with the evaluate polling `location.hostname` until my own
navigation landed, then reading the target article through the live page's own same-origin `fetch` (credentials
omitted) and rendering the parsed document into the live DOM so `innerText`/table layout resolved normally. Hidden
scenario-tab panels on GameWith 257432 were read directly out of the DOM rather than by clicking each tab, which is
how the per-scenario tables in section 4 were recovered. No page in this ledger was skipped or filled from memory.

Server tagging: every rule below is `[JP]`. Global confirmation was not sought for any item, so nothing here
is tagged `[Global]` or `[Both]`. Treat the whole file as JP-wiki-sourced.

Reading convention used throughout: `JP string` = verbatim text as printed; `EN` = my mechanical translation.

---

### 1. Failure mechanics

#### 1.1 Can training fail at all, and per-discipline

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| 賢さ以外のトレーニングを行うと体力を消費する | Only non-Wit training spends energy | GameWith 257432 | [JP] |
| 賢さのトレーニングには体力消費がない上、わずかに体力を回復できる。他のトレーニングと比較すると失敗率の上がり方が緩い | Wit training spends no energy and refunds a little; its failure-rate ramp is gentler than the other four | GameWith 257432 | [JP] |
| 体力が低いとトレーニング失敗率が高くなる | Lower energy raises failure probability | GameWith 257432 / Game8 372572 / Kamigame 164094542528651333 | [JP] |
| 賢さ失敗では発生しない / 賢さで失敗時はステータスが上がらず体力だけ回復する | Wit training CAN fail, but on failure no failure event fires: no stat gain, and energy still recovers | GameWith 257432 | [JP] |
| 友情トレーニングでも失敗はする | Rainbow/friendship training can also fail | Kamigame 149493615532457367 | [JP] |
| トレーニングに失敗すると、能力は上昇せず、体力低下・やる気低下・ケガ（能力低下）のいずれかのペナルティが発生します | On failure: no stat gain, plus exactly one of energy loss, motivation loss, injury (stat loss) | Game8 372572 | [JP] |
| 失敗率が高いほど失敗した時のペナルティも大きくなってしまう | Penalty magnitude scales with the failure rate itself | Game8 372572 | [JP] |

#### 1.2 Failure-rate formula (published as a calculation, not a lookup table)

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| 失敗率×失敗率ダウン+コンディション補正=失敗率 | Failure rate = (base failure rate x failure-rate-down multiplier) + condition correction | GameWith 274990 | [JP] |
| 失敗率ダウンは、トレーニング失敗率に対して乗算と思われる。また、練習上手◯などコンディション補正による効果は最終的に加算されるため、練習ベタや小さなほころびの効果を0にすることはできない | Failure-rate-down is multiplicative; condition corrections are added last, so bad-condition penalties cannot be zeroed out | GameWith 274990 | [JP] |
| 体力が少ない状態でトレーニングを行うことで、トレーニングに失敗する場合がある | Low energy is the trigger condition | GameWith 257432 | [JP] |
| 失敗のデメリットが大きい | (qualitative) failure carries heavy downside | GameWith 257432 | [JP] |

Note on the multiplicative wording: GameWith prints `失敗率×失敗率ダウン+コンディション補正=失敗率`. Read literally the
factor is `1 - ダウン率`, since the same page describes 失敗率ダウン as reducing the rate. The page does not disambiguate.

#### 1.3 Published probability table by energy level

❌ UNVERIFIED: No energy-vs-failure-rate probability table is printed on any page rendered here. Searched/checked:
GameWith 257432 (トレーニングの効果と失敗イベント), GameWith 274990 (サポート効果と計算式), GameWith 293379
(失敗率ダウン持ちサポートカード一覧), GameWith 257614 (育成の攻略とコツ), Game8 372572 (トレーニング効果とおすすめ),
Game8 454202 (友情トレーニング), Kamigame 164094542528651333 (体力の使い道と回復方法), Kamigame 146276970408242410
(トレーニングの効果と優先度). All of them state the relationship only qualitatively.

The nearest thing to a numeric threshold published by these wikis is an operator heuristic, not a game table:

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| 練習の成功率は体力50以上とそれ以下で大きく変わってくる。…体力はなるべく50以上をキープしよう | Practical cutoff used by the editors: success rate differs markedly at 50 energy vs below 50 | GameWith 257614 | [JP] (editorial heuristic, not a printed game table) |
| 体力5割未満か強い練習が無い時はお休み | Rest when energy is below 50% | Kamigame 114672877806026759 | [JP] (editorial heuristic) |
| 体力が半分以下になった場合、お休みを選ぶのがおすすめ | Same, below half | Kamigame 147307607101602682 | [JP] (editorial heuristic) |

Energy is printed as a 0..100 scale on GameWith (recovery amounts 30/50/70 and costs -19..-28 per training are all
on that scale) and the wikis consistently treat 100 as the cap. See section 4.4 for the printed per-training energy costs.

#### 1.4 The three failure outcomes and their numeric ranges

GameWith 257432 prints the failure outcome set as two named event variants, each with two choices. `直前のトレーニング
に応じたステータス` = the stat matching the training just attempted.

Event `お大事に！` (Take care! / the "get well" failure event):

| JP string as printed | EN mechanical translation | Tag |
|---|---|---|
| 上選択肢: やる気ダウン / 直前のトレーニングに応じたステータス-5 / ランダムで『練習下手』になる | Top choice: motivation down 1 tier; the training's primary stat -5; random chance to gain 練習下手 (Practice Clumsy) | [JP] |
| 下選択肢: やる気ダウン / 直前のトレーニングに応じたステータス-10 / ランダムで『練習下手』になる / 『練習上手◯』になる | Bottom choice: motivation down 1 tier; primary stat -10; random chance of 練習下手, or gain 練習上手◯ (Practice Skilled ◯) | [JP] |

Event `無茶は厳禁！` (Don't overdo it! / the "no reckless training" failure event):

| JP string as printed | EN mechanical translation | Tag |
|---|---|---|
| 上選択肢: 体力+10 / やる気ダウン / 直前のトレーニングに応じたステータス-10 / 5種ステータスからランダムに2種を-10 / ランダムで『練習ベタ』になる | Top choice: energy +10; motivation down 1 tier; primary stat -10; two of the five stats chosen at random -10 each; random chance to gain 練習ベタ (Practice Untalented) | [JP] |
| 下選択肢: やる気ダウン / 直前のトレーニングに応じたステータス-10 / 5種ステータスからランダムに2種を-10 / 『練習ベタ』になる / 体力+10 / 『練習上手◯』になる | Bottom choice: motivation down 1 tier; primary stat -10; two random stats -10 each; gain 練習ベタ, or energy +10 and 練習上手◯ | [JP] |

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| トレーニング失敗時は2種類の失敗イベントが発生する。ウマ娘ごとに選択肢の文言が違うが内容は同じ | Exactly 2 failure-event variants exist; wording differs per character but the numbers do not | GameWith 257432 | [JP] |
| ケガ（能力低下） | Injury is expressed as a stat drop, not a separate counter | Game8 372572 | [JP] |

Numeric range summary across both event variants: primary stat `-5` to `-10`; secondary random stat loss `0` or
`-10 x 2`; energy `0` or `+10`; motivation `-1 tier`.

#### 1.5 What reduces the failure chance

Support effect `失敗率ダウン` (failure-rate-down), printed per support card and per limit-break level by GameWith 293379.
Header note on that page: `固有ボーナスとの合計値を記載` = values shown already include the card's own unique bonus.

| JP card name (EN) | Lv30 無凸 | Lv35 1凸 | Lv40 2凸 | Lv45 3凸 | Lv50 4凸 | Tag |
|---|---|---|---|---|---|---|
| マチカネフクキタル (Machikane Tannhauser) | 10% | 10% | 10% | 10% | 10% | [JP] |
| 駿川たづな (Tazuna Hayakawa) | 30% | 32% | 35% | 37% | 40% | [JP] |
| 樫本理子 (Riko Kashimoto) | 25% | 26% | 28% | 30% | 30% | [JP] |
| ライトハロー (Light Hello) | 20% | 22% | 25% | 27% | 30% | [JP] |
| 佐岳メイ (Mei Satake) | 15% | 16% | 18% | 20% | 20% | [JP] |
| 都留岐涼花 (Ryoka Tsurugi) | 20% | 22% | 25% | 27% | 30% | [JP] |
| 秋川理事長 (Chairman Akikawa) | 15% | 16% | 18% | 20% | 20% | [JP] |
| タッカーブライン (Tucker Brine) | 15% | 16% | 18% | 20% | 20% | [JP] |
| 保科健子 (Kenko Hoshina) | 10% | 11% | 13% | 15% | 15% | [JP] |
| カジノドライヴ (Casino Drive) | 10% | 11% | 13% | 15% | 15% | [JP] |
| 駿川たづな (Tazuna Hayakawa, 2nd entry) | 10% | 11% | 13% | 15% | 15% | [JP] |
| 桐生院葵 (Aoi Kirisouin) | 30% | 31% | 32% | 33% | 35% | [JP] |
| 駿川たづな (Tazuna Hayakawa, 3rd entry) | 15% | 16% | 17% | 18% | 20% | [JP] |
| 桐生院葵 (Aoi Kirisouin, 2nd entry) | 15% | 16% | 17% | 18% | 20% | [JP] |
| 樫本理子 (Riko Kashimoto, 2nd entry) | 10% | 11% | 13% | 15% | 15% | [JP] |
| ライトハロー (Light Hello, 2nd entry) | 15% | 16% | 17% | 18% | 20% | [JP] |
| 佐岳メイ (Mei Satake, 2nd entry) | 15% | 16% | 17% | 18% | 20% | [JP] |
| 都留岐涼花 (Ryoka Tsurugi, 2nd entry) | 15% | 16% | 17% | 18% | 20% | [JP] |
| 秋川理事長 (Chairman Akikawa, 2nd entry) | 5% | 6% | 8% | 10% | 10% | [JP] |
| タッカーブライン (Tucker Brine, 2nd entry) | 5% | 6% | 8% | 10% | 10% | [JP] |
| 保科健子 (Kenko Hoshina, 2nd entry) | 5% | 6% | 8% | 10% | 10% | [JP] |
| カジノドライヴ (Casino Drive, 2nd entry) | 5% | 6% | 8% | 10% | 10% | [JP] |

(The page does not print the rarity/type next to each row in the plain-text rendering; duplicate names are separate
cards and are kept as separate rows rather than merged.)

Condition-based failure-rate modifiers (Game8 374294, `トレーニング失敗率に影響`):

| JP string as printed | EN mechanical translation | Tag |
|---|---|---|
| 練習上手◯: トレーニング失敗率が−2%される | Practice Skilled ◯: failure rate -2 percentage points | [JP] |
| 練習ベタ: トレーニング失敗率が+2%される | Practice Untalented: failure rate +2 percentage points | [JP] |
| 練習上手◎: トレーニング失敗率が−4%される (ナリタタイシン固有コンディション) | Practice Skilled ◯◯: failure rate -4 points, Narita Taishin exclusive condition | [JP] |
| 小さなほころび: トレーニング失敗率が+5%される (スーパークリーク固有) | Small Fray: failure rate +5 points, Super Creek exclusive condition | [JP] |
| 大輪の輝き: トレーニング失敗率が−5%される (スーパークリーク固有) | Grand Bloom: failure rate -5 points, Super Creek exclusive condition | [JP] |
| 「練習ベタ」と「練習上手◯」は、お互いに上書きしあう | Practice Untalented and Practice Skilled ◯ overwrite each other | [JP] |
| 「練習ベタ」に関しては体力に関係なく失敗率が発生する | Practice Untalented raises the failure rate independently of energy | [JP] |
| 小さなほころび: クラシック2月後半に育成イベントで取得 / クラシック10月後半の育成イベント以外では解消できない | Small Fray is granted in Classic-class late February and only clears at the Classic late-October event | [JP] |
| 大輪の輝き: クラシック10月後半に育成イベントで取得 / 獲得と同時に「小さなほころび」が治る | Grand Bloom is granted Classic late October and clears Small Fray at the same time | [JP] |
| 練習上手◎: シニア3月前半に「練習ベタ」を取得していなければ取得 | Practice Skilled ◯◯ is granted Senior-class early March only if you do not hold Practice Untalented | [JP] |

Other failure-reducing systems printed:

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| 夏合宿は試食会の失敗率軽減100％を活かして練習を4連続で踏む | The new scenario's 試食会 (tasting session) grants 100% failure-rate reduction during summer camp | GameWith scenario article 257614 / Kamigame scenario page | [JP] |

❌ UNVERIFIED: skills that reduce the training failure rate. The task named `スタートコスパ◎` and `排気量アップ`-style
training skills. I read GameWith 257432, 274990, 293379, 257614, Game8 372572/374294, and Kamigame training pages. None
of them lists any race/skill that changes the training failure rate. Every failure-rate lever printed on these pages is
either a support effect (`失敗率ダウン`), a condition (練習上手◯/◎, 練習ベタ, 小さなほころび, 大輪の輝き), or a scenario
gimmick. Do not model race skills as failure-rate modifiers.

#### 1.6 Items that affect failure / energy (searched)

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| やる気UPスイーツ: ウマ娘のやる気を絶好調にする | Motivation Up Sweets: sets motivation straight to Peak Condition | GameWith 258369 (アイテム一覧) | [JP] |
| 目覚まし時計: 育成の目標レースでコンテニューができる | Alarm Clock: continue after a target race | GameWith 258369 | [JP] |
| にんじんBBQセット: サポート全員の絆ゲージ+5【使用タイミング】育成序盤に使用 | Carrot BBQ Set: all support bond +5, use early (Climax-scenario item) | Kamigame 149493615532457367 | [JP] |
| リセットホイッスル: 練習メンバーを再配置【使用タイミング】合宿時に使用 | Reset Whistle: re-seats training participants, use during camp | Kamigame 149493615532457367 | [JP] |
| メガホン / アンクル: 練習効率を高める【使用タイミング】合宿時に使用 / 複数人の友情トレーニング発生時に使用 | Megaphone / Ankle: raise training efficiency, use during camp and when multiple rainbow sessions fire | Kamigame 149493615532457367 | [JP] |

❌ UNVERIFIED: `アイシング` (icing). I searched GameWith's アイテム一覧 (258369, updated 2026-09-16), the rest/health
articles, and GameWith/Game8/Kamigame link indexes for `ケア` and `アイシング`. No page rendered here prints an アイシング
command or item, so there is no printed recovery value for it. Do not add it.

---

### 2. Rest and outing

#### 2.1 `お休み` (Rest), energy restored

> **Owner ruling 2026-10-03 (Dispatch A, Stage 1).** `docs/UMAMUSUME_REFERENCE.md` §1.1.5 now cites this
> table for the modal +50 reading and lists +70 (big success) and +30 (failure, sometimes with 夜ふかし気味)
> as outcome tiers. The earlier "+30 per standard rest" line there was the low-tier outcome printed by
> GameWith 257614, not the common rest. The §1.1.6 line "rest returns +30" sits outside this dispatch's
> fence and stays as it was; reconcile it on the next doc pass that reaches §1.1.6.

GameWith 257617 (`お休みの確率と効果｜発生イベント`, 最終更新 2021年7月5日10:15), n = 3833 trials:

| JP string as printed | 確率 (probability) | 回数 (count) | EN mechanical translation | Tag |
|---|---|---|---|---|
| 70回復 | 25.4% | 975/3833 | Rest outcome: energy +70 | [JP] |
| 50回復 | 58.1% | 2226/3833 | Rest outcome: energy +50 | [JP] |
| 30回復 | 12.8% | 492/3833 | Rest outcome: energy +30 | [JP] |
| 30回復+夜ふかし | 3.7% | 140/3833 | Rest outcome: energy +30 and gain 夜ふかし気味 (Late-Night Feeling) | [JP] |

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| お休みを選択すると1ターン消費し、ウマ娘の体力を50回復する | Rest costs 1 turn and restores 50 energy (the modal case) | GameWith 257617 | [JP] |
| 大成功した場合は70回復できるが、失敗した場合はデメリットもある | Big-success roll gives 70; the failure roll carries a downside | GameWith 257617 | [JP] |
| 確率的には50>70>30>夜ふかしとなっており | Outcome frequency order: 50 > 70 > 30 > late-night | GameWith 257617 | [JP] |
| お休みの失敗イベントは回復量が30に減少し、やる気ダウンや「夜ふかし気味」になる | Rest failure event: recovery drops to 30, motivation down, and 夜ふかし気味 may be gained | GameWith 257617 | [JP] |
| お休みでは体力+30のパターンで稀に『夜更かし気味』になってしまいます | The +30 rest outcome is where the rare late-night condition comes from | GameWith 257614 | [JP] |
| 夏合宿中の休憩はお出かけとセットになっており、体力が40回復するだけでなくやる気アップの効果もある | During summer camp the break is bundled with an outing: energy +40 AND motivation up | GameWith 257617 | [JP] |
| 合宿期間は、お出かけのコマンドが選択できなくなる。その分、お休みのコマンドで回復+やる気アップができる | During camp the Outing command is unavailable, so Rest supplies recovery plus motivation | GameWith 257538 | [JP] |

Kamigame 147307607101602682 (`お休みのおすすめタイミングと寝不足イベントの発生確率`, 最終更新日 2022-12-19 10:22),
n = 100 trials. Same event names, different printed event names and split:

| JP string as printed | 確率（回数） | EN mechanical translation | Tag |
|---|---|---|---|
| 休息はバッチリ！ 体力+70 | 12％（12/100回） | Event "Rest is perfect!", energy +70 | [JP] |
| リフレッシュ完了 体力+50 | 66％（66/100回） | Event "Refresh complete", energy +50 | [JP] |
| 寝不足で…… 体力+30 確率で「夜ふかし気味」獲得 | 22％（22/100回） | Event "Under-slept...", energy +30, chance of late-night | [JP] |
| 寝不足で…… 体力+30「夜ふかし気味」なし | 17％（17/100回） | Under-slept branch with no condition | [JP] |
| 寝不足で…… 体力+30「夜ふかし気味」獲得 | 5％（5/100回） | Under-slept branch that grants the condition | [JP] |

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| 寝不足イベントが発生する確率は約22％ | The under-slept event fires ~22% of rests | Kamigame 147307607101602682 | [JP] |
| お休みで夜ふかし気味を獲得する確率は約5％ | The late-night condition itself lands ~5% of rests | Kamigame 147307607101602682 | [JP] |
| 1ターンで最大70回復可能 | Rest caps at +70 in one turn | Kamigame 147307607101602682 | [JP] |
| 寝不足イベント発生時は、体力は30しか回復しない | With the under-slept event only 30 is restored | Kamigame 147307607101602682 | [JP] |

Kamigame 164094542528651333 (`体力の使い道と回復方法`, 最終更新日 2021-10-15 17:35):

| JP string as printed | EN mechanical translation | Tag |
|---|---|---|
| お休みの回復量は+30、+50、+70のいずれかである。また、確率で寝不足になる可能性がある | Rest returns one of +30 / +50 / +70, with a chance of under-slept | [JP] |

#### 2.2 `夜ふかし気味` (Late-Night Feeling) and its cure

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| 夜ふかし気味: ランダムで体力−10、やる気−1 | While held: random energy -10 and motivation -1 | Game8 374294 | [JP] |
| 夜ふかし気味で……: 選択肢なし・体力-10・稀にやる気-1 | Its recurring event, no choice: energy -10, rarely motivation -1 | Game8 417620 | [JP] |
| イベントは体力減少のみの場合と体力に加えやる気も下がる2パターンあります | Two patterns: energy-only drop, and energy plus motivation drop | Game8 417620 | [JP] |
| 寝不足で…… 【パターン1】・体力+30【パターン2】・体力+30・やる気−1・「夜ふかし気味」を取得 | Rest under-slept event has 2 printed patterns | Game8 417620 | [JP] |
| 保健室は体力20回復に加えてバッドコンディションを治す効果がある。ただし成功率があり、失敗することも | Nurse's office: energy +20 plus an attempt to clear one bad condition; the attempt can fail | GameWith 286339 | [JP] |
| 練習ベタ(計119回): 成功84%(100回) 失敗16%(19回) / 夜ふかし(計111回): 成功84.68%(94回) 失敗15.32%(17回) | Observed cure rate 84% (100/119) and 84.68% (94/111) | GameWith 286339 | [JP] |
| バッドコンディションは85%で治り、15%で失敗すると推測が立てられる | Editors' inferred rule: 85% cure, 15% fail | GameWith 286339 | [JP] (an estimate from their own sampling, stated as 推測) |
| 練習ベタ(計100回): 1回 86%(86回) / 2回 10%(10回) / 3回 3%(3回) / 4回 1%(1回) | Tries-to-cure distribution for Practice Untalented | GameWith 286339 | [JP] |
| 夜ふかし(計94回): 1回 87.23%(82回) / 2回 8.51%(8回) / 3回 3.19%(3回) / 4回 1.06%(1回) | Tries-to-cure distribution for late-night | GameWith 286339 | [JP] |
| 確率はバッドコンディション共通である可能性が高い | The cure probability is likely shared across all bad conditions | GameWith 286339 | [JP] |
| 複数のバッドコンディションがある場合にも1つしか治せず効率が悪いため | The office clears only one condition per visit | Kamigame 164086749461487685 | [JP] |
| 夏合宿中は保健室が使えない / クラシック・シニア級における7〜8月は夏合宿があり、保健室が使えません | The nurse's office is unavailable during summer camp (July-early September, Classic and Senior years) | Game8 417620 | [JP] |
| 「駿川たづな」の2・4段階目のお出かけイベントには一部の悪いコンディションを確定で治す効果 | Tazuna Hayakawa outing stages 2 and 4 cure certain bad conditions with certainty | Game8 417620 / GameWith 286339 | [JP] |
| 夏合宿中にお休みをすると、バッドコンディションを全て解消できる | Resting during summer camp clears every bad condition | Kamigame 164086749461487685 | [JP] |
| 神社でお祈り！イベントが発生し、バッドコンディションが治る。ただし治らない場合もあるので確実性はない | The shrine sub-roll at an outing can cure a bad condition, non-deterministically | GameWith 263413 | [JP] |

#### 2.3 `お出かけ` (Outing): mood deltas and energy

GameWith 263413 (`お出かけの効果･確率とおすすめタイミング`, 最終更新 2023年3月6日14:21):

| JP string as printed | EN mechanical translation | Tag |
|---|---|---|
| お出かけを選択すると1段階以上のやる気アップとランダムで体力回復が発生。効果は選べないものの、確実にやる気が1段階は上がる | Outing: motivation up by at least 1 tier, guaranteed; energy recovery is random | [JP] |
| カラオケはやる気が2段階上昇するお出かけイベント | Karaoke raises motivation 2 tiers | [JP] |
| お散歩では体力+10とやる気が1段階上昇する | Walk: energy +10, motivation +1 tier | [JP] |
| 大吉: 体力+30、やる気アップ | Shrine, Great Blessing: energy +30, motivation up | [JP] |
| 中吉: 体力+20、やる気アップ | Shrine, Middle Blessing: energy +20, motivation up | [JP] |
| 小吉: 体力+10、やる気アップ | Shrine, Small Blessing: energy +10, motivation up | [JP] |
| クレーンゲームはクラシック級以降でおでかけした際に、1度だけランダムで発生する | Crane game: one random occurrence from Classic class onward, grants skill hints | [JP] |

GameWith 263413 outing-type frequency table (n = 119):

| JP string as printed | 回数 | 確率 | EN mechanical translation | Tag |
|---|---|---|---|---|
| カラオケ | 43/119 | 36.13% | Karaoke | [JP] |
| お散歩 | 35/119 | 29.41% | Walk | [JP] |
| 神社合計 | 41/119 | 34.45% | Shrine, all fortunes | [JP] |
| 神社-小吉 | 23/119 | 19.33% | Shrine, Small Blessing | [JP] |
| 神社-中吉 | 14/119 | 11.76% | Shrine, Middle Blessing | [JP] |
| 神社-大吉 | 4/119 | 3.36% | Shrine, Great Blessing | [JP] |

Kamigame 147307607101602682 outing-vs-rest summary:

| JP string as printed | EN mechanical translation | Tag |
|---|---|---|
| お出かけの特徴:・やる気が1〜2段階UPする・体力が0〜30回復する・確率でバッドコンディションを解消できる・友人キャラとのお出かけでは体力を大きく回復できる | Outing: motivation +1 to +2 tiers, energy +0 to +30, probabilistic condition cure; a Friend-type outing recovers far more | [JP] |
| お休みの特徴:・体力が30〜70回復する・稀にバッドコンディション「夜ふかし気味」を獲得する | Rest: energy +30 to +70, rarely the late-night condition | [JP] |

Kamigame 146407792159257608 (`お出かけ発生条件と連続イベント一覧`, 最終更新日 2024-04-14 13:50): the Friend-card outing
chains, printed per stage. These are the exact per-turn deltas the guide states.

Light Hello (`ライトハロー`) chain:

| Stage | JP string as printed | EN mechanical translation | Tag |
|---|---|---|---|
| 1回目 | 体力の最大値+4・体力+27・やる気+1・ライトハローの絆ゲージ+5 | Max energy +4; energy +27; motivation +1; bond with Light Hello +5 | [JP] |
| 2回目 | 体力+27・やる気+1・根性+11・ライトハローの絆ゲージ+5 | Energy +27; motivation +1; Guts +11; bond +5 | [JP] |
| 3回目 選択肢【恥ずかしくないですよ】 | 体力+70・やる気+1・ライトハローの絆ゲージ+5 | Energy +70; motivation +1; bond +5 | [JP] |
| 3回目 選択肢【わかりました！】 | やる気+1・スピード+16・根性+16・ライトハローの絆ゲージ+5 | Motivation +1; Speed +16; Guts +16; bond +5 | [JP] |
| 4回目 | 体力+30・やる気+1・根性+11・ライトハローの絆ゲージ+5 | Energy +30; motivation +1; Guts +11; bond +5 | [JP] |
| 5回目【大成功】 | やる気+1・体力+30・スピード+10・根性+10・絆ゲージ+5・レアスキル「お先に失礼っ！」のヒントLv+3 | Motivation +1; energy +30; Speed +10; Guts +10; bond +5; rare-skill hint level +3 | [JP] |
| 5回目【成功】 | やる気+1・体力+20・スピード+5・根性+5・絆ゲージ+5・レアスキル「お先に失礼っ！」のヒントLv+1 | Motivation +1; energy +20; Speed +5; Guts +5; bond +5; rare-skill hint level +1 | [JP] |

Throne-assembled (`玉座に集いし者たち`) chain, per character variant:

| JP string as printed | EN mechanical translation | Tag |
|---|---|---|
| シンボリルドルフ: 体力+12・賢さ+34・「闘争心」のヒントLv+1・絆ゲージ+5 | Energy +12; Wit +34; hint level +1; bond +5 | [JP] |
| トウカイテイオー: 体力+12・やる気+1・スピード+23・「ポジションセンス」のヒントLv+1・絆ゲージ+5 | Energy +12; motivation +1; Speed +23; hint level +1; bond +5 | [JP] |
| ツルマルツヨシ: 体力+36・スキルPt+17・「フルスロットル」のヒントLv+1・絆ゲージ+5 | Energy +36; skill points +17; hint level +1; bond +5 | [JP] |

Team Sirius (`チームシリウス`) chain, printed variants:

| JP string as printed | EN mechanical translation | Tag |
|---|---|---|
| マックイーン: 体力の最大値+4・スタミナ+12・根性+12・賢さ+12・「あやしげな作戦」のヒントLv+2・絆ゲージ+5 | Max energy +4; Stamina +12; Guts +12; Wit +12; hint level +2; bond +5 | [JP] |
| ライスシャワー: 体力+32・スキルPt+18・「徹底マーク◯」のヒントLv+2・絆ゲージ+5 | Energy +32; skill points +18; hint level +2; bond +5 | [JP] |

Friend-card outing unlock, printed condition:

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| 絆ゲージを3まで上げると、お出かけイベントが発生する | The Friend outing chain unlocks at bond gauge 3 bars | Kamigame 164094542528651333 | [JP] |
| 友人/グループタイプの絆ゲージが3本目に到達して緑色になっているとお出かけ開始イベントが発生しやすい | The start event is likelier once the bar reaches the 3rd (green) segment | GameWith 257614 | [JP] |
| 友人/グループタイプのサポカは最低でも1度は一緒にトレーニングをしていないとお出かけ開始イベントが発生しない | The Friend/Group card must join at least one training before the outing chain can start | GameWith 257614 | [JP] |

#### 2.4 `疲労` and `ケガ` as separate systems

❌ UNVERIFIED: no separate `疲労` (fatigue) counter and no separate `ケガ` (injury) counter with numeric ranges is
printed on any page rendered here. GameWith and Kamigame model energy as the single `体力` gauge; Game8 372572 collapses
injury into the failure outcome as `ケガ（能力低下）`, i.e. an stat drop rather than a timed injury state. If the app needs
a fatigue or injury column, it is not sourced from these three wikis.

---

### 3. Motivation / mood (`やる気`), the five tiers

Tier names, JP as printed on all three sites, in order: `絶好調` / `好調` / `普通` / `不調` / `絶不調`.
EN: Peak Condition / Good Condition / Normal / Poor Condition / Worst Condition.
(Note GameWith and Kamigame print `普通`; Game8 372572 prints `普通` in the tier table and `やる気が「普通」状態` in prose.)

Two mutually inconsistent sets of multipliers are printed across these wikis. Both are recorded.

Set A, GameWith 257538 (`やる気の影響と上げ方`, 最終更新 2021年9月22日17:37):

| JP string as printed | EN mechanical translation | Tag |
|---|---|---|
| 絶好調: トレーニングの効果が20%上昇 / レース中、基礎能力が4%上昇 | Peak: training effect x1.20; in-race base stats +4% | [JP] |
| 好調: トレーニングの効果が10%上昇 / レース中、基礎能力が2%上昇 | Good: training effect x1.10; in-race base stats +2% | [JP] |
| 普通: 補正なし | Normal: no correction | [JP] |
| 不調: トレーニングの効果が10%減少 / レース中、基礎能力が2%減少 | Poor: training effect -10%; in-race base stats -2% | [JP] |
| 絶不調: トレーニングの効果が20%減少 / レース中、基礎能力が4%減少 | Worst: training effect -20%; in-race base stats -4% | [JP] |

Set B, Game8 372572 and Kamigame 146417173592559219 + 146276970408242410 (identical text on both pages):

| JP string as printed | EN mechanical translation | Tag |
|---|---|---|
| 【絶好調】トレーニングの効果が20％上昇する / レース中、基礎能力が10％上昇する | Peak: training effect +20%; in-race base stats +10% | [JP] |
| 【好調】トレーニングの効果が10％上昇する / レース中、基礎能力が5％上昇する | Good: training effect +10%; in-race base stats +5% | [JP] |
| 【普通】トレーニングの効果に増減無し / レース中、基礎能力の増減無し | Normal: no change on either | [JP] |
| 【不調】トレーニングの効果が10％減少する / レース中、基礎能力が2％減少する | Poor: training effect -10%; in-race base stats -2% | [JP] |
| 【絶不調】トレーニングの効果が20％減少する / レース中、基礎能力が5％減少する | Worst: training effect -20%; in-race base stats -5% | [JP] |

The training-effect column agrees across all three sites (20/10/0/-10/-20). Only the in-race base-stat column
disagrees; see `## Disagreements`.

GameWith also prints the mood factor as used in the training formula, on 274990
(`やる気効果アップ`, 最終更新 2022年5月18日12:04). This is the signed delta form, and it is asymmetric:

| JP string as printed | 育成ウマ娘のやる気による変化 | EN mechanical translation | Tag |
|---|---|---|---|
| 絶好調 | 1.2 | Peak factor 1.2 | [JP] |
| 好調 | 1.1 | Good factor 1.1 | [JP] |
| 普通 | 0 | Normal factor 0 | [JP] |
| 不調 | -0.9 | Poor factor -0.9 (i.e. 0.9, a 10% cut) | [JP] |
| 絶不調 | -0.8 | Worst factor -0.8 (i.e. 0.8, a 20% cut) | [JP] |

| JP string as printed | EN mechanical translation | Tag |
|---|---|---|
| やる気効果アップ 計算式: 育成ウマ娘のやる気×(1+やる気効果A+B)=やる気効果量 | Motivation-effect-up multiplies the tier delta: tier factor x (1 + summed support bonus) | [JP] |
| やる気効果が高いほど、絶好調の時の上昇量も上がるが、絶不調の時の減少量も大きくなる。基準値はそれぞれ1と思われる | A higher motivation-effect bonus amplifies both the peak gain and the worst-case loss; baseline is 1 | [JP] |
| (training formula) (基準値+ステアップボーナス)×成長率×(1+調子×やる気効果)×トレーニング効果アップ×友情ボーナス×参加人数補正 | Full stat-gain chain: (base + stat bonus) x growth rate x (1 + mood x motivation effect) x training-effect-up x friendship bonus x participant count | [JP] |
| トレーニングが成功すると、スキルPtが獲得できる | Skill points only land on a successful training | Kamigame 146276970408242410 | [JP] |
| 育成ウマ娘のやる気が低下すると、ステータスだけではなく、獲得スキルPtも減少します | Motivation drops cut skill points too, not just stats | GameWith 274990 | [JP] |

What triggers a tier change:

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| やる気が低いときはお出かけコマンドを選ぶことでやる気を上げられる | The Outing command raises a tier | GameWith 257538 | [JP] |
| 合宿期間は…お休みのコマンドで回復+やる気アップができる | During camp, Rest raises a tier | GameWith 257538 | [JP] |
| ターンが経過したタイミングでシナリオイベントやキャライベントが発生する。イベントでは…やる気に関係するものも存在する | Turn-advancing events can move the tier | GameWith 257538 | [JP] |
| ▲やる気上昇。すでに絶好調の場合は変動なし。 | A motivation-up event at Peak does nothing | GameWith 257538 | [JP] |
| 育成開始直後はやる気が普通状態から始まる | A run starts at Normal | GameWith 263413 | [JP] |
| 確率でカラオケを引ければいきなり絶好調に | Karaoke can jump a fresh run from Normal to Peak | GameWith 263413 | [JP] |
| 駿川たづなを編成している場合は、お出かけ後やる気1段階が上がった状態で次にトレーニングで選択すると絶好調に | With Tazuna in the deck, one outing then one choice lands Peak | GameWith 263413 | [JP] |
| ランダムで体力とやる気が下がる (夜ふかし気味) | Late-Night Feeling pushes motivation down at random | Game8 374294 | [JP] |
| 幸運体質: 育成中の「やる気」ダウンを一度だけ防ぐ | Lucky Constitution blocks one motivation drop | Game8 374294 | [JP] |
| ポジティブ思考: 悪いコンディションを1度だけ防ぐ | Positive Thinking blocks one bad condition | Game8 374294 | [JP] |
| 片頭痛: やる気が上がらなくなる | Migraine forbids motivation increases | Game8 374294 | [JP] |
| 肌あれ: ランダムでやる気-1 | Rough Skin drops motivation by 1 tier at random | Game8 374294 | [JP] |
| やる気UPスイーツ: ウマ娘のやる気を絶好調にする | Motivation Up Sweets forces Peak | GameWith 258369 | [JP] |

---

### 4. The Wit loop (`賢さ`)

#### 4.1 Energy behaviour: Wit refunds rather than spends

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| 賢さ以外のトレーニングを行うと体力を消費する | Every non-Wit discipline spends energy | GameWith 257432 | [JP] |
| スピード/スタミナ/パワー/根性の4つのトレーニングは、行うたびに体力を消費する | Speed/Stamina/Power/Guts each cost energy per use | GameWith 257432 | [JP] |
| 賢さのトレーニングには体力消費がない上、わずかに体力を回復できる | Wit costs nothing and adds a little energy | GameWith 257432 | [JP] |
| 賢さ: ・賢さ(大up)・スピード(小up)・スキルpt(大up)・体力回復(微量) | Wit raises Wit (large), Speed (small), skill points (large), and recovers a trace of energy | GameWith 257432 | [JP] |
| 賢さ【上昇】・体力・スピード・賢さ・スキルPt / 【下降】なし | Wit training lists energy as a stat that RISES; no decrease column | Game8 372572 | [JP] |
| 基本は体力を消費して基礎能力やスキルPtが上昇しますが、「賢さ」のトレーニングは体力を消費せず、他のトレーニングよりもスキルPtが多く貰えます | Wit is the only discipline with no energy cost and a higher skill-point payout | Game8 372572 | [JP] |
| 賢さトレーニングをする…僅かだが体力を回復できる | Kamigame states the same | Kamigame 164094542528651333 | [JP] |
| 賢さ練習では体力回復ができるので体力があまり減っていないなら賢さ練習で回復しよう | Editor rule: substitute Wit training for Rest when energy is only mildly low | GameWith 257614 | [JP] |

❌ UNVERIFIED: no dedicated Wit training-efficiency guide exists on the three wikis rendered here. The Kamigame page
linked as 賢さ (page/146301691988282803) is a support-card ranking with card scores only and no training mechanics; I
rendered it and confirmed that. GameWith's Wit mechanics live inside トレーニングの効果と失敗イベント (257432) and
サポート効果と計算式 (274990); Game8's live inside トレーニング効果とおすすめ (372572) and 友情トレーニング (454202).
There is no standalone 賢さ 効率 guide to cite, so nothing in section 4 is drawn from one.

#### 4.2 Printed per-session Wit values, per scenario (GameWith 257432, 最終更新 2023年2月25日17:08)

These were hidden behind a scenario tab control (`w-toggle-switch`, tabs `URA / アオハル / クライマックス / グランドライブ /
グランドマスターズ`). The tab is not visible in a plain text scrape; I read every panel out of the DOM directly.
`体力` here is the energy delta, positive = refund.

| Scenario | Lv | スピード (Speed) | 賢さ (Wit) | 体力 (energy) | スキルpt (skill points) | Tag |
|---|---|---|---|---|---|---|
| URA | 1 | +2 | +9 | **+5** | +4 | [JP] |
| URA | 2 | +2 | +10 | **+5** | +4 | [JP] |
| URA | 3 | +2 | +11 | **+5** | +4 | [JP] |
| URA | 4 | +3 | +12 | **+5** | +4 | [JP] |
| URA | 5 | +4 | +13 | **+5** | +4 | [JP] |
| アオハル (Aoharu) | 1 | +2 | +6 | **+5** | +5 | [JP] |
| アオハル | 2 | +2 | +7 | **+5** | +5 | [JP] |
| アオハル | 3 | +2 | +8 | **+5** | +5 | [JP] |
| アオハル | 4 | +3 | +9 | **+5** | +5 | [JP] |
| アオハル | 5 | +4 | +10 | **+5** | +5 | [JP] |
| クライマックス (Climax) | 1 | +2 | +6 | **+5** | +3 | [JP] |
| クライマックス | 2 | +2 | +7 | **+5** | +3 | [JP] |
| クライマックス | 3 | +2 | +8 | **+5** | +3 | [JP] |
| クライマックス | 4 | +3 | +9 | **+5** | +3 | [JP] |
| クライマックス | 5 | +4 | +10 | **+5** | +3 | [JP] |
| グランドライブ (Grand Live) | 1 | +2 | +6 | **+5** | +5 | [JP] |
| グランドライブ | 2 | +2 | +7 | **+5** | +5 | [JP] |
| グランドライブ | 3 | +2 | +8 | **+5** | +5 | [JP] |
| グランドライブ | 4 | +3 | +9 | **+5** | +5 | [JP] |
| グランドライブ | 5 | +4 | +10 | **+5** | +5 | [JP] |
| グランドマスターズ (Grand Masters) | 1 | +2 | +8 | **+5** | +5 | [JP] |
| グランドマスターズ | 2 | +2 | +9 | **+5** | +5 | [JP] |
| グランドマスターズ | 3 | +2 | +10 | **+5** | +5 | [JP] |
| グランドマスターズ | 4 | +3 | +11 | **+5** | +5 | [JP] |
| グランドマスターズ | 5 | +4 | +12 | **+5** | +5 | [JP] |

The energy refund is a flat `+5` at every training level in every scenario printed. It does not scale with level.

#### 4.3 Wit skill-point payout versus the other disciplines

Same page, same tab control. Skill-point base per discipline:

| Scenario | スピード | スタミナ | パワー | 根性 | 賢さ | Tag |
|---|---|---|---|---|---|---|
| URA | +2 | +2 | +2 | +2 | +4 | [JP] |
| アオハル | +4 | +4 | +4 | +4 | +5 | [JP] |
| クライマックス | +2 | +2 | +2 | +2 | +3 | [JP] |
| グランドライブ | +4 | +4 | +4 | +4 | +5 | [JP] |
| グランドマスターズ | +5 | +5 | +5 | +5 | +5 | [JP] |

Cross-check printed by GameWith 274990: `スキルPtボーナス…基準値は賢さトレーニングが4、それ以外が2となる` = base skill
points are 4 for Wit and 2 for everything else (the URA figures), additive bonuses on top.

In Grand Masters the payout column collapses to 5 across the board; Wit's advantage there is the energy refund, not
the skill points.

#### 4.4 Full printed base values, all disciplines, all scenarios (GameWith 257432)

Energy costs are printed as negative numbers. These are the only per-discipline energy costs any page here prints, and
they are what a failure-rate model would need as the spending half of the loop.

URA:

| Training | Lv | primary | secondary | energy |
|---|---|---|---|---|
| スピード (Speed) | 1 | スピード +10 | パワー +5 | -21 |
| スピード | 2 | +11 | +5 | -22 |
| スピード | 3 | +12 | +5 | -23 |
| スピード | 4 | +13 | +6 | -25 |
| スピード | 5 | +14 | +7 | -27 |
| スタミナ (Stamina) | 1 | スタミナ +9 | 根性 +4 | -19 |
| スタミナ | 2 | +10 | +4 | -20 |
| スタミナ | 3 | +11 | +4 | -21 |
| スタミナ | 4 | +12 | +5 | -23 |
| スタミナ | 5 | +13 | +6 | -25 |
| パワー (Power) | 1 | パワー +8 | スタミナ +5 | -20 |
| パワー | 2 | +9 | +5 | -21 |
| パワー | 3 | +10 | +5 | -22 |
| パワー | 4 | +11 | +6 | -24 |
| パワー | 5 | +12 | +7 | -26 |
| 根性 (Guts) | 1 | 根性 +8 | スピード +4, パワー +4 | -22 |
| 根性 | 2 | +9 | +4, +4 | -23 |
| 根性 | 3 | +10 | +4, +4 | -24 |
| 根性 | 4 | +11 | +5, +4 | -26 |
| 根性 | 5 | +12 | +5, +5 | -28 |
| 賢さ (Wit) | 1..5 | 賢さ +9..+13 | スピード +2..+4 | **+5** |

アオハル (Aoharu):

| Training | Lv 1..5 primary | secondary | energy |
|---|---|---|---|
| スピード | +8, +9, +10, +11, +12 | パワー +4, +4, +4, +5, +6 | -19, -20, -21, -23, -25 |
| スタミナ | +8, +9, +10, +11, +12 | 根性 +6, +6, +6, +7, +8 | -17, -18, -19, -21, -23 |
| パワー | +9, +10, +11, +12, +13 | スタミナ +4, +4, +4, +5, +5 | -18, -19, -20, -22, -24 |
| 根性 | +6, +7, +8, +9, +10 | スピード +3,+3,+3,+4,+4 / パワー +3,+3,+3,+3,+4 | -20, -21, -22, -24, -26 |
| 賢さ | +6, +7, +8, +9, +10 | スピード +2, +2, +2, +3, +4 | **+5** flat |

クライマックス (Climax):

| Training | Lv 1..5 primary | secondary | energy |
|---|---|---|---|
| スピード | +8, +9, +10, +11, +12 | パワー +4, +4, +4, +5, +6 | -19, -20, -21, -23, -25 |
| スタミナ | +7, +8, +9, +10, +11 | 根性 +3, +3, +3, +4, +5 | -17, -18, -19, -21, -23 |
| パワー | +6, +7, +8, +9, +10 | スタミナ +4, +4, +4, +5, +6 | -18, -19, -20, -22, -24 |
| 根性 | +6, +7, +8, +9, +10 | スピード +3,+3,+3,+4,+4 / パワー +3,+3,+3,+3,+4 | -20, -21, -22, -24, -26 |
| 賢さ | +6, +7, +8, +9, +10 | スピード +2, +2, +2, +3, +4 | **+5** flat |

グランドライブ (Grand Live):

| Training | Lv 1..5 primary | secondary | energy |
|---|---|---|---|
| スピード | +8, +9, +10, +11, +12 | パワー +4, +4, +4, +5, +6 | -19, -20, -21, -23, -25 |
| スタミナ | +8, +9, +10, +11, +12 | 根性 +6, +6, +6, +7, +8 | -20, -21, -22, -24, -26 |
| パワー | +9, +10, +11, +12, +13 | スタミナ +4, +4, +4, +5, +6 | -20, -21, -22, -24, -26 |
| 根性 | +7, +8, +9, +10, +11 | スピード +2,+2,+2,+3,+3 / パワー +2,+2,+2,+2,+3 | -20, -21, -22, -24, -26 |
| 賢さ | +6, +7, +8, +9, +10 | スピード +2, +2, +2, +3, +4 | **+5** flat |

グランドマスターズ (Grand Masters):

| Training | Lv 1..5 primary | secondary | energy |
|---|---|---|---|
| スピード | +10, +11, +12, +13, +14 | パワー +3, +3, +3, +4, +5 | -19, -20, -21, -23, -25 |
| スタミナ | +8, +9, +10, +11, +12 | 根性 +6, +6, +6, +7, +8 | -20, -21, -22, -24, -26 |
| パワー | +9, +10, +11, +12, +13 | スタミナ +4, +4, +4, +5, +6 | -20, -21, -22, -24, -26 |
| 根性 | +9, +10, +11, +12, +13 | スピード +2,+2,+2,+3,+3 / パワー +3,+3,+3,+3,+4 | -20, -21, -22, -24, -26 |
| 賢さ | +8, +9, +10, +11, +12 | スピード +2, +2, +2, +3, +4 | **+5** flat |

Scenario-label mapping: the tab control's `input[type=radio]` ids are `tsRadio1-1`..`tsRadio1-5` with labels
`URA / アオハル / クライマックス / グランドライブ / グランドマスターズ`, and I resolved each label to its panel by
matching `data-toggle-name` plus `data-toggle-value` on a second pass. The mapping used above is confirmed by that join,
not assumed from DOM order.

#### 4.5 Wit loop supporting rules

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| 同じトレーニングを4回行うと、トレーニングLvが1つ上がる。最大5Lvまで上がり、体力消費が増える代わりに能力の上昇率が上がる | Each discipline levels after 4 uses, cap Lv5; higher level costs more energy and yields more | Kamigame 146276970408242410 | [JP] |
| URAやクライマックスでは各種のトレーニングを4回行うと、アオハル杯ではチームステータスによってトレーニングLvを上げられる | Level-up rule differs by scenario | GameWith 257432 | [JP] |
| 毎年1回、7月前半〜8月後半の4ターンの間は、5種類全てのトレーニングレベルが最大のLv5になる | Summer camp: all five disciplines are Lv5 for 4 turns (July-early to August-late) | Kamigame 146276970408242410 | [JP] |
| 賢さトレーニングは、体力を回復しつつ絆ゲージを上げることができます | Wit training builds bond gauge while refunding energy | Game8 454202 | [JP] |
| 賢さ1200を超えていると、固有スキル、進化スキル、レアスキルの速度を上げる効果と前に出る効果が上昇する | Above Wit 1200, unique/evolved/rare skill speed and front-running effects scale further | Game8 372949 | [JP] |
| 賢さ: 掛かり状態のなりにくさ・レース運びの上手さ・スキルの発動率 | Wit governs over-exertion resistance, route handling, and skill activation rate | Game8 372572 / 372949 | [JP] |

Other printed stat thresholds from Game8 372949 (`ステータスの意味と影響する要素`, 最終更新日 2026.09.24 12:46):

| JP string as printed | EN mechanical translation | Tag |
|---|---|---|
| スピードが2000を超えるとレース終盤に全開スパート効果が発動 | Speed above 2000 unlocks the full-throttle spurt effect | [JP] |
| スタミナが1200を超えていると、スタミナ勝負効果が使用 | Stamina above 1200 unlocks the stamina-duel effect | [JP] |
| パワーは…1200を超えていると脚をためる効果が使用 | Power above 1200 unlocks the leg-holding effect | [JP] |
| 短距離: 1400m以下 / マイル: 1401m~1800m / 中距離: 1801m~2400m / 長距離: 2401m以上 | Distance bands | [JP] |

---

#### 4.6 Wit rainbow energy refund, per card (the escalation term in the Wit loop)

GameWith 293360 (`賢さ友情回復量アップ持ちサポートカード一覧`, 最終更新 2026年9月14日10:10). These are the `A+B` bonus
values that GameWith 274990 adds onto the printed flat base of `5` (`基準回復値の5`). `-` is the value the page prints
where the effect has not unlocked at that limit break.

Value ladder `3 / 3 / 4 / 4 / 5` across Lv30(無凸), Lv35(1凸), Lv40(2凸), Lv45(3凸), Lv50(4凸), shared by:
ファインモーション, エアシャカール, ユキノビジン, セイウンスカイ, ナイスネイチャ, ミホノブルボン, カレンチャン,
ナリタタイシン, ニシノフラワー, サトノダイヤモンド, シリウスシンボリ, ミスターシービー, ライスシャワー,
マチカネタンホイザ, ナカヤマフェスタ, トウカイテイオー, オグリキャップ, エアグルーヴ, スイープトウショウ, ヒシアケボノ,
メジロラモーヌ, テイエムオペラオー, ダイタクヘリオス, マンハッタンカフェ, メジロマックイーン, ノースフライト,
ネオユニヴァース, コパノリッキー, タイキシャトル, シンボリクリスエス, シーザリオ, ダイワスカーレット,
アグネスデジタル, シンボリルドルフ, イクノディクタス, デアリングタクト, デアリングハート, エアメサイア,
ウインバリアシオン, クロノジェネシス, デュランダル, サイレンススズカ, フォーエバーヤング, フサイチパンドラ.

| JP card / rarity group | Lv30 | Lv35 | Lv40 | Lv45 | Lv50 | Tag |
|---|---|---|---|---|---|---|
| Standard SSR group (list above) | 3 | 3 | 4 | 4 | 5 | [JP] |
| フジキセキ / ダイワスカーレット / アグネスタキオン / マーベラスサンデー / マチカネフクキタル / メジロドーベル / メジロアルダン / アイネスフウジン / スイープトウショウ / ゴールドシチー / セイウンスカイ / マルゼンスキー / タイキシャトル / タニノギムレット / ダンツフレーム / サクラローレル / ラッキーライラック / ビコーペガサス / ヒシアマゾン | 3 | 3 | 3 | 3 | 4 | [JP] |
| シンボリルドルフ / ダイワスカーレット / ファインモーション / アグネスタキオン / エアシャカール / マーベラスサンデー / マチカネフクキタル (lower group) | 2 | 2 | 2 | 2 | 3 | [JP] |
| ビワハヤヒデ | 2 | 2 | 2 | 2 | 3 | [JP] |
| メジロドーベル (R) | - | - | - | 1 | 5 | [JP] |
| ナイスネイチャ (R) | - | 1 | 3 | 3 | 3 | [JP] |
| レッドディザイア | - | - | - | - | 5 | [JP] |

Effective Wit rainbow refund on a flat-base-5 rule: `5 + bonus`. A 4-凸 standard Wit card therefore returns `5 + 5 = 10`
energy, versus `5` printed in the base-value tables when no rainbow fires.

#### 4.7 Training level progression, which drives the Wit curve

GameWith 257618 (`トレーニングレベルの上げ方と必要回数`, 最終更新 2024年2月26日19:36):

| JP string as printed | EN mechanical translation | Tag |
|---|---|---|
| トレーニングにはレベルが1~5まで存在し…基本的にどのトレーニングも初期値は1から始まる | Level range 1..5, every discipline starts at 1 | [JP] |
| URAファイナルズ / クライマックス / グランドライブ: 4回刻みで上昇する | Those three scenarios: +1 level every 4 selections | [JP] |
| 合宿中のトレーニングはカウントされない | Camp selections do not count toward level-up | [JP] |
| レベル1 初期 / レベル2 4回 / レベル3 8回 / レベル4 12回 / レベル5 16回 | Cumulative selections to reach each level: 0, 4, 8, 12, 16 | [JP] |
| アオハル杯: レベル1 評価G~F / レベル2 評価E~D / レベル3 評価C~B / レベル4 評価A / レベル5 評価S | Aoharu maps team rating to level: G-F=1, E-D=2, C-B=3, A=4, S=5 | [JP] |
| アオハル杯: トレーニング回数では上がらない | Aoharu levels never advance by repetition | [JP] |
| プロジェクトL'Arc: 期待度が20,60,100で全トレがLv1上昇 / 40,80 は上昇なし | L'Arc: expectation 20, 60 and 100 raise every facility by 1; 40 and 80 raise nothing | [JP] |
| UAFシナリオではトレーニングLvの概念は存在しない | UAF has no training level at all, only the competing-event level | [JP] |



### 5. Friendship / rainbow training trigger (`友情トレーニング` / `虹トレーニング`)

#### 5.1 The bond gauge threshold

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| サポートキャラの絆ゲージをオレンジ色以上にすることで、友情トレーニングが発生するようになる | Rainbow training unlocks once bond reaches orange | GameWith 257432 | [JP] |
| 絆ゲージがオレンジ以上でサポートカードとタイプが一致しているトレーニングを行うと、友情トレーニングが発生する | Orange plus matching specialty type fires it | GameWith 257607 | [JP] |
| 条件1：絆ゲージがオレンジ色である / 絆ゲージをオレンジ（4メモリ）以上にする | Condition 1: bond at orange, i.e. 4 memory segments | Game8 372572 | [JP] |
| 絆ゲージをオレンジ（80）以上にする / 4メモリ目の「80」まで行くとゲージがオレンジ色に変わり | The orange mark is the numeric value 80, the 4th segment | Game8 454202 | [JP] |
| 友情トレーニングを発生させるには、サポートの絆ゲージを80まで上げる必要がある。絆ゲージ80は色だと緑の次であるオレンジ、ゲージの区切りだと4ゲージ目 | Threshold is 80; green is the 3rd, orange the 4th | Kamigame 149493615532457367 | [JP] |
| 絆ゲージは初期値0、最大値100となる。初期値が増えることで80以上で発生する友情トレーニングまでに必要な絆ゲージが減る | Bond gauge runs 0 to 100; rainbow threshold is 80 | GameWith 274990 | [JP] |
| 絆ゲージが8割以上になると友情練習が可能になる | Rainbow at 80% of the gauge | Kamigame 114672877806026759 | [JP] |

Consensus numeric threshold: `80` on a `0..100` bond gauge, 4th segment, colour orange (`オレンジ`).
Second condition, printed identically on all three sites: the support card's specialty type (`得意タイプ`) must match
the training facility chosen.

#### 5.2 Bond gauge gain per action

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| ウマ娘のいるトレーニングに参加: +7 / ∟「！」マークのついたウマ娘: +5 | Joining the training a card is at: +7; if that card carries a `!` mark: +5 instead | Game8 454202 | [JP] |
| サポートカードイベント: +5〜+10(+5がほとんど) | Support card events: +5 to +10, mostly +5 | Game8 454202 | [JP] |
| にんじんBBQセット(クライマックスシナリオ限定): 全サポート+5 | Carrot BBQ Set (Climax only): every support +5 | Game8 454202 | [JP] |
| サポートの絆ゲージはトレーニングをすると5上がり | Training gives +5 bond | Kamigame 149493615532457367 | [JP] |
| 「！」の付いたウマ娘は、絆ゲージが更に+5されます(2人以上いる時はランダムで1人) | A `!` card gives a further +5, one random card only if several qualify | Game8 454202 | [JP] |
| 愛嬌◯: サポートの絆ゲージ上昇量が+2される | Condition 愛嬌◯ (Charming ◯): bond gain +2 | Game8 374294 / Kamigame 149493615532457367 | [JP] |
| 注目株: 記者と理事長の絆ゲージ上昇量が+2される | Rising Star: reporter and chairman bond gain +2 | Game8 374294 | [JP] |

Note: Game8 prints +7 per training and Kamigame prints +5. Both are recorded under `## Disagreements`.

#### 5.3 Rainbow-training multipliers and the published calculation

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| (トレーニングの基礎値+ボーナス値)×成長率ボーナス×やる気(1+やる気ボーナス×やる気効果アップ)/100×トレーニング効果アップ×友情ボーナス×参加人数ボーナス(1人につき5%) | Full rainbow training chain, with the participant bonus printed as 5% per person | Game8 454202 | [JP] |
| 友情ボーナスの計算式: 1.25×1.3=1.625 | Worked example: a +25% card times a +30% card equals x1.625 | Game8 454202 | [JP] |
| キタサンブラック 友情ボーナス+25% / ナリタトップロード 友情ボーナス+30% | Kitasan Black +25% friendship bonus; Narita Top Road +30% | Game8 454202 | [JP] |
| 友情ボーナスは、キャラごとの値が乗算される | Per-card friendship bonuses multiply, they do not add | Game8 454202 | [JP] |
| 計算式: 基準値×(1+友情ボナA/100)×(1+友情ボナB/100) | Same rule restated with the percentage divided by 100 | Kamigame 149493615532457367 | [JP] |
| 友情トレーニング自体にボーナス値はありません。存在はしませんが、もし仮に友情ボーナスが無いカードと友情トレーニングをした場合、通常のトレーニング全く同じ数値の上昇値となります | Rainbow-ness itself carries no bonus; only the card's 友情ボーナス value does. A zero-bonus card gives a plain-training result | GameWith 274990 | [JP] |
| およそ1.5倍ほど多くもらえる | Rainbow training yields roughly 1.5x | Game8 372572 | [JP] (editorial approximation, not a formula) |
| サポート効果（友情トレーニングの発生していない参加者）: トレーニング効果アップ / やる気効果アップ / ステータスボーナス | Non-rainbow participants still contribute training-effect-up, motivation-effect-up and stat bonus to the calculation | Game8 454202 | [JP] |
| 虹色でない参加者も一部の効果が乗る / 参加キャラのタイプが一致していない場合、友情ボーナスは計算に適応されません | Type-mismatched participants contribute no friendship bonus | Game8 454202 | [JP] |
| サポートキャラクターの絆ゲージを…友情トレーニングが発生するようになる。友情トレーニングが発生することで、通常のトレーニングより多くステータスを上げられる | Rainbow gives more stat than a normal session | GameWith 257432 / 257606 | [JP] |

#### 5.4 Named support-effect identifiers cited

These wikis name effects, they do not publish numeric effect ids. The names as printed, from GameWith 274990's
25-effect table (`全25種類のサポート効果が存在`):

| JP effect name | EN mechanical translation | Printed definition | Tag |
|---|---|---|---|
| 友情ボーナス | Friendship Bonus | Boost from a rainbow session firing | [JP] |
| 失敗率ダウン | Failure Rate Down | Failure rate when training together is reduced | [JP] |
| 体力消費ダウン | Energy Cost Down | Energy spent when training together is reduced | [JP] |
| トレーニング効果アップ | Training Effect Up | Stat gain when training together is increased | [JP] |
| やる気効果アップ | Motivation Effect Up | The mood correction's magnitude is amplified | [JP] |
| ステータスボーナス | Stat Bonus | Added to the base value before multipliers, per stat | [JP] |
| スキルPtボーナス | Skill Pt Bonus | Skill point gain when training together is increased | [JP] |
| 賢さ友情回復量アップ | Wit Rainbow Recovery Up | Energy refunded by a Wit rainbow session is increased | [JP] |
| 得意率アップ | Specialty Rate Up | Appearance rate of the card's specialty facility | [JP] |
| 初期絆ゲージ | Initial Bond Gauge | Bond gauge at run start | [JP] |
| ヒントLvアップ / ヒント発生率アップ | Hint Level Up / Hint Occurrence Rate Up | Hint level and hint-event probability | [JP] |
| レースボーナス / ファン数ボーナス | Race Bonus / Fan Count Bonus | Post-race stat, skill-point and fan rewards | [JP] |
| イベント回復量アップ / イベント効果アップ | Event Recovery Up / Event Effect Up | Energy and stat gains from that card's events | [JP] |
| 初期ステータスアップ | Initial Stat Up | Fixed addition to start-of-run stats, unaffected by growth rate | [JP] |

| JP string as printed | EN mechanical translation | Tag |
|---|---|---|
| 固有ボーナスとサポート効果に同じ効果がある時は重複する | A card's unique bonus stacks with its own support effect of the same kind | [JP] |
| (計算式) 基準消費量-(基準消費量×体力消費ダウン)=体力消費量 / 消費量に対して乗算で計算されて端数は切り上げ | Energy cost down multiplies the cost, remainder rounds UP | [JP] |
| (計算式) 基準回復値+賢さ友情回復量A+B=体力回復量 / 全ての賢さ友情回復量を加算した数値+基準回復値の5。ただし友情トレーニングが発生していないと効果がない | Wit rainbow recovery = flat base 5 plus summed bonus; requires the rainbow to fire | [JP] |
| (計算式) 上昇値×(1+トレーニング効果A×B) / トレーニング効果は全て加算と思われる | Training effect up stacks additively | [JP] |
| (計算式) 基準値+(スキルPtボーナスA+B)=スキルPt上昇量 | Skill point bonus stacks additively on the base | [JP] |
| (計算式) 基準値(約18%)×得意率=得意トレーニング出現率 | Specialty rate: base ~18% per facility times the specialty-rate value | [JP] |
| 全く出現しない確率が約10％だったため、トレーニング出現率が90%、各トレーニング毎は約18%と仮定 | Modelled appearance rates: 90% overall, ~18% per facility | [JP] (GameWith's own verification estimate) |
| (計算式) 基準値×ヒント発生率アップ / ヒント発生率基準値は約6％前後と思われる | Hint-rate base ~6%, multiplied by the hint-rate-up value | [JP] (estimate) |

❌ UNVERIFIED: numeric effect ids. No page rendered here prints an integer effect id, enum value, or database key.
They publish effect names in Japanese plus percentage values only.

---

### Appendix: printed values not covered above

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| 追加の自主トレ 選択肢①: 対応するトレーニングの能力+5 / 体力-5 ; 選択肢②: 体力+5 | Extra self-training event: option 1 gives that discipline's stat +5 and costs 5 energy; option 2 gives +5 energy | GameWith 257614 | [JP] |
| あんし〜ん笹針師 秘孔を狙う(成功): 5種ステータス+20 / (失敗): やる気ダウン・全ステータス-15・夜ふかし気味になる | Acupuncturist event option 1: all five stats +20 on success; motivation down, all stats -15, late-night on failure | GameWith 257614 | [JP] |
| (成功): コーナー回復◯取得・直線回復取得 / (失敗): 体力-20・やる気ダウン | Option 2: two corner/straight recovery skills on success; energy -20 and motivation down on failure | GameWith 257614 | [JP] |
| (成功): 体力の最大値+12・体力+40 / (失敗): 体力-20・やる気ダウン・練習ベタになる | Option 3: max energy +12 and energy +40 on success; energy -20, motivation down, Practice Untalented on failure | GameWith 257614 | [JP] |
| (成功): 体力+20・やる気アップ・愛嬌○になる / (失敗): 体力-10・やる気ダウン | Option 4: energy +20, motivation up, Charming ◯ on success; energy -10 and motivation down on failure | GameWith 257614 | [JP] |
| 不安なのでやめておく: 体力+10 | Option 5 (decline): energy +10 | GameWith 257614 | [JP] |
| 1日10回までしかレンタルできない | Rented ancestor: 10 borrows per day | GameWith 257614 | [JP] |
| レンタル回数が増える…ウマプランの購入でも増やすことができます | Campaigns and the Trainer Plan subscription raise the daily borrow cap | GameWith 257614 | [JP] |
| 夏合宿は7月前半から8月後半までの4ターン続く | Summer camp is 4 turns | GameWith 257614 | [JP] |
| 太り気味: トレーニングでスピードが上がらなくなる / スピード上昇量が0 | Weight Gain: training Speed gain becomes exactly 0; event stat gains are unaffected | Game8 374294 | [JP] |
| なまけ癖: 確率でトレーニングが無効になる / ウマ娘がトレーニングに来ない | Lazy Habit: the session is voided at random | Game8 374294 | [JP] |
| まだまだ準備中: レース出走後に確率で体力-5 (メイショウドトウ固有) | Still Preparing, Meisho Doto exclusive condition: energy -5 after a race, at random | Game8 374294 | [JP] |
| 切れ者: スキル獲得に必要なSPが全て10%軽減 | Clever: all skill costs reduced 10% | Game8 374294 | [JP] |
| ファンとの約束: 指定レース勝利で能力アップ (スマートファルコン固有) | Promise to Fans: nominated-race win grants a stat bump | Game8 374294 | [JP] |
| リフレッシュの心得 (グッドコンディション一覧に掲載) | Listed as a good condition; no numeric effect printed on the pages read | Game8 417620 | [JP] |

---

### Source Ledger

| URL | 最終更新日 | what it contributed |
|---|---|---|
| https://gamewith.jp/uma-musume/article/show/257614 | 2026年9月25日02:39 | Rest +30 late-night case, energy-50 operator threshold, Wit-as-recovery rule, camp 4 turns, 10/day ancestor borrows, self-training and acupuncturist event numbers, Friend outing unlock, rainbow-session camp failure note |
| https://gamewith.jp/uma-musume/article/show/257432 | 2023年2月25日17:08 | Core failure rules, both failure events with exact deltas, Wit cannot fire a failure event, full per-scenario per-level base-value tables behind the scenario tab control, discipline-to-stat map |
| https://gamewith.jp/uma-musume/article/show/257617 | 2021年7月5日10:15 | Rest outcome probabilities with raw counts (n=3833), camp rest +40 with motivation, nurse's-office cure of late-night |
| https://gamewith.jp/uma-musume/article/show/257538 | 2021年9月22日17:37 | Five mood tiers with training +20/-20 and race +4/-4, camp Rest gives recovery plus motivation, events can't exceed Peak |
| https://gamewith.jp/uma-musume/article/show/257606 | 2021年2月26日20:44 | Rainbow definition, orange bond precondition, specialty-type match requirement |
| https://gamewith.jp/uma-musume/article/show/257607 | 2021年2月26日20:54 | Bond gauge raised by training and by events; orange gate drives rainbow and some events |
| https://gamewith.jp/uma-musume/article/show/263413 | 2023年3月6日14:21 | Outing outcome table with counts (n=119), Karaoke 2 tiers, Walk +10 energy, shrine fortunes +10/+20/+30, crane game, run starts at Normal |
| https://gamewith.jp/uma-musume/article/show/274990 | 2022年5月18日12:04 | Failure-rate formula, mood factor table 1.2/1.1/0/-0.9/-0.8, bond gauge 0..100 with rainbow at 80, Wit rainbow base recovery 5, skill-point bases 4 Wit / 2 other, all 25 support effect names, specialty ~18%, hint ~6% |
| https://gamewith.jp/uma-musume/article/show/286339 | 2021年7月2日18:21 | Nurse's office energy +20, cure rate 84%/84.68% with raw counts, 15%-85% inferred rule, tries-to-cure distribution, camp blocks the office |
| https://gamewith.jp/uma-musume/article/show/293379 | 2026年9月14日10:10 | Failure-rate-down support card table by limit break, 22 rows, plus the unique-bonus inclusion note |
| https://gamewith.jp/uma-musume/article/show/257618 | 2024年2月26日19:36 | Level curve 0/4/8/12/16 selections, camp sessions excluded from the count, Aoharu rating-to-level map, L'Arc expectation 20/60/100, UAF has no training level |
| https://gamewith.jp/uma-musume/article/show/293360 | 2026年9月14日10:10 | Wit rainbow energy-refund bonus per support card by limit break, the 3/3/4/4/5, 3/3/3/3/4 and 2/2/2/2/3 ladders and the unlocked-later rows |
| https://gamewith.jp/uma-musume/article/show/258369 | 2026年9月16日16:03 | Consumable item list, used to confirm アイシング is absent and to source Motivation Up Sweets and the Alarm Clock |
| https://gamewith.jp/uma-musume/article/show/317671 | 2026年9月26日21:34 | Scenario stat caps per scenario, scenario release dates, used only to confirm scenario naming |
| https://game8.jp/umamusume/372572 | 2026.09.10 13:35 | Failure outcome trio (energy/motivation/injury), penalty scales with failure rate, mood table with race 10%/5% variant, Wit no-cost statement, rainbow ~1.5x, 4 trainings per level, orange = 4 segments |
| https://game8.jp/umamusume/454202 | 2025.11.21 01:50 | Bond threshold printed as 80, bond gain +7 (or +5 with `!`), event gain +5..+10, BBQ set +5, full training formula with 5% participant bonus, friendship bonus multiply example 1.25x1.3=1.625 |
| https://game8.jp/umamusume/372949 | 2026.09.24 12:46 | Wit/Speed/Stamina/Power stat gates 2000 and 1200, distance bands 1400/1800/2400 |
| https://game8.jp/umamusume/374294 | 2025.11.20 22:11 | Full condition table with failure-rate deltas (-2/+2/-4/+5/-5), bond gain +2 conditions, mood-block and stat-block conditions, overwrite rule |
| https://game8.jp/umamusume/417620 | 2025.11.20 22:11 | Late-Night Feeling per-turn deltas, rest event patterns, office blocked in camp, Tazuna outing stages 2 and 4 cure certain |
| https://game8.jp/umamusume/372297 | 2026.09.25 19:43 | New scenario resource table (base gauge 10/5/3, hint items, outing condiment counts), current scenario lineup |
| https://kamigame.jp/umamusume/page/164094542528651333.html | 2021-10-15 17:35 | Rest outcomes +30/+50/+70, Wit refund statement, Friend outing at bond 3, energy drop raises injury likelihood |
| https://kamigame.jp/umamusume/page/114672877806026759.html | 2025-03-07 14:10 | Cross-scenario standing rules: bond 80 for rainbow, rest below 50% energy, keep Peak, camp rainbow x4 target, bad condition to office |
| https://kamigame.jp/umamusume/page/147307607101602682.html | 2022-12-19 10:22 | Named rest events with percentages (n=100): +70 12%, +50 66%, +30 22%, late-night 5%; rest-vs-outing comparison |
| https://kamigame.jp/umamusume/page/146407792159257608.html | 2024-04-14 13:50 | Friend-card outing chains with exact per-stage numbers, unlock event chains per card |
| https://kamigame.jp/umamusume/page/146417173592559219.html | 2021-10-15 16:53 | Five mood tiers with training +20/-20 and race +10/-5 |
| https://kamigame.jp/umamusume/page/149493615532457367.html | 2022-12-19 10:17 | Rainbow threshold 80 as the 4th segment, bond +5 per training, friendship bonus multiply formula, rainbow can fail, camp item list |
| https://kamigame.jp/umamusume/page/146276970408242410.html | 2021-10-15 17:10 | 4 trainings per level, Lv5 cap, camp all facilities at Lv5 for 4 turns, mood tier table |
| https://kamigame.jp/umamusume/page/164086749461487685.html | 2024-04-15 14:02 | Bad-condition cure route ranking, office clears only one, camp rest clears all, shrine less reliable than office |
| https://kamigame.jp/umamusume/page/146301691988282803.html | 2026-08-31 14:56 | Wit support-card ranking page; rendered successfully but contains no training mechanics, only card scores |

---

### Disagreements

| Topic | Value A | Value B | Value C |
|---|---|---|---|
| Mood effect on in-race base stats | GameWith 257538: 絶好調 +4%, 好調 +2%, 不調 -2%, 絶不調 -4% | Game8 372572: 絶好調 +10%, 好調 +5%, 不調 -2%, 絶不調 -5% | Kamigame 146417173592559219 and 146276970408242410: identical to Game8 (+10/+5/-2/-5) |
| Mood effect on training | No disagreement. All three print +20/+10/0/-10/-20 | same | same |
| Rest outcome probabilities | GameWith 257617 (n=3833): +70 25.4%, +50 58.1%, +30 12.8%, +30 with late-night 3.7% | Kamigame 147307607101602682 (n=100): +70 12%, +50 66%, +30 22% of which late-night 5% | Same outcome set, materially different distribution; GameWith's sample is 38x larger |
| Rest event names | GameWith prints recovery amounts only (`70回復`, `50回復`, `30回復`, `30回復+夜ふかし`) | Kamigame prints named events (`休息はバッチリ！`, `リフレッシュ完了`, `寝不足で……`) | Not a numeric conflict, but the two wikis index the same outcomes differently |
| Bond gauge gain per training | Game8 454202: `+7` for joining a card's training, `+5` when the card carries `!` | Kamigame 149493615532457367: `トレーニングをすると5上がり` | Both agree the gauge tops out around 80 for rainbow |
| Guts training secondary stats | GameWith 257432: 根性 raises 根性(大), スピード(中), パワー(中) | Game8 372572: 根性 raises スピード, パワー, 根性(大) | Consistent, no conflict on direction, only on the large/medium labeling |
| Stamina training secondary | GameWith 257432: スタミナ(大up), 根性(中up) | Game8 372572: スタミナ(大アップ), 根性, スキルPt | Same |
| Rainbow strength | Game8 372572: `およそ1.5倍` (approximately 1.5x), stated as an approximation | GameWith 274990: `友情トレーニング自体にボーナス値はありません`, the multiplier comes only from each card's 友情ボーナス, e.g. 1.25 x 1.30 = 1.625 | Kamigame gives only the multiply formula, no flat estimate |
| Failure penalty shape | GameWith 257432: two named events with fixed choice-dependent numbers (stat -5 or -10, sometimes two extra stats -10, energy 0 or +10) | Game8 372572: `体力低下・やる気低下・ケガ（能力低下）のいずれか`, i.e. one of three, and magnitude scales with failure rate | The two describe different shapes; GameWith enumerates the event outcomes, Game8 describes the outcome class. Neither publishes a per-rate penalty table |
| Injury as a system | Game8 372572 lists ケガ as a failure outcome | GameWith 257432 lists no injury timer, only stat drops and 練習ベタ/練習下手 conditions | No page read prints injury duration or severity numbers |
| Wit skill-point payout | GameWith 274990: `基準値は賢さトレーニングが4、それ以外が2` | GameWith 257432 per-scenario tables: URA 4 vs 2, Climax 3 vs 2, Aoharu 5 vs 4, Grand Live 5 vs 4, Grand Masters 5 vs 5 | The 4-vs-2 statement is URA-specific; the scenario tables are the general rule |

Excluded from the ledger deliberately: no prediction model, no race-simulation rule, and no real-world ancestor
information appeared on any page in a form I could tie to a training mechanic, so none is recorded.

---

## Our Grand Concert: the 2026-10-05 primary read (Game8 ×2, GameTora, `[Global]` notice 899)

**Server:** `[Global]`, with the `[JP]` client strings quoted where GameTora prints them.
**Read:** 2026-10-05. **Method:** raw HTTP fetch with a browser user-agent, HTML kept under the gitignored
`research-scratch/gc-2026-10-05/` (`to-text.cjs` does the tag strip), then read to the end of the body. Nothing
below came from a search snippet.

| Source | Grade | Page date | HTTP | Bytes | File |
|---|---|---|---|---|---|
| [Game8 Grand Live (Grand Concert) Scenario Guide 607337](https://game8.co/games/Umamusume-Pretty-Derby/archives/607337) | A | last updated 2026-07-26 | 200 | 510,262 | `g8-607337.html` |
| [Game8 Fully Charged Explained 607687](https://game8.co/games/Umamusume-Pretty-Derby/archives/607687) | A | last updated 2026-07-02 04:58 | 200 | 395,943 | `g8-607687.html` |
| [GameTora Our Grand Concert (Grand Live) Scenario](https://gametora.com/umamusume/our-grand-concert) | B | last updated 2026-07-22 | 200 | 173,502 | `gt-gc.html` |
| [Umamusume Global Official News 905](https://umamusume.com/news/905), the scenario add notice | S | posted 2026-07-22 22:00 UTC | 200 through the news API | 2,328 chars of body | read live, and the source of every client string below |
| [Umamusume Global Official News 899](https://umamusume.com/news/899), "New Career Scenario Celebration Pretty Derby and Support Card Scouts out now!" | S | posted 2026-07-22 22:00 (UTC) | rendered in the browser | 5,511 chars of body text | read live, page not cached |
| [Game8 List of All Songs 537610](https://game8.co/games/Umamusume-Pretty-Derby/archives/537610) | A | not re-read this pass | 200 | fetched, title confirmed, body not extracted | `g8-songslist.html` |
| [Kamigame JP guide to earning パフォーマンス](https://kamigame.jp/umamusume/page/225259740320523907.html) | A `[JP]` | 2025-06-07 from the search index, **not the page's own stamp**; treat as ⚠️ STALE | 200 | 150,022 | `jp-perf.html`. Used for one thing only: the `[JP]` resource noun is パフォーマンス, the head noun of its title. Its advice body was not extracted |

This closes the re-opening bar `docs/UMAMUSUME_REFERENCE.md` §2.2.4 set for itself: one of the two Game8 pages
read end to end. Both were. The 2026-09-27 owner suspension that required it is discharged by that read plus
the owner's 2026-10-05 instruction to research this scenario and update the documents.

### Game8 607337, verbatim extraction

Scenario framing, in the page's own words:

> ・Grand Live returns the standard trainee career with a blend of more Idol-performance themes. The gameplay
> loop is similar to Unity Cup (Aoharu Hai) where, instead of improving Training through your Unity Cup team,
> Songs and Techniques are learned for stronger training gains.
> ・ Friendship Training Effectiveness is strongly encouraged in this scenario, compared to the previous
> Race-stacking strategy of Trackblazer (MANT) where training turns are mostly kept to a minimum or limited to
> Summer Camp periods.

Career length and cross-scenario advice, as printed: "Each career run is expected to last an average of around
30 minutes", shorter than Trackblazer's near hour; PvP-built Umamusume (Champions Meeting and Team Trials) are
"better trained in Grand Live compared to past scenarios"; "Strong parents are still best made in Trackblazer
(MANT) for race affinity bonuses and spark farming"; fan farming stays optimal in Trackblazer or URA Finale.

Returns the standard career:

> Unlike in Trackblazer (MANT) , Grand Live (Grand Concert) returns the standard trainee career features of
> race goals and career events. This includes the familiar New Year events, the hot spring lottery, and hidden
> events for each Umamusume. Because of this, expect easier access to skill-locked hidden events. This means you
> will be able to get the Runaway style for Umamusume like Silence Suzuka , and skills like Straightaway Spurt
> will be easier to obtain for Mayano Top Gun .

The resource, as the page lists it under "Focus on Promo Concerts and Training": `Performance Points`, in five
types printed as **Dance (Da)**, **Passion (Pa)**, **Vocals (Vo)**, **Visuals (Vi)**, **Composure (Co)**.

> Grand Live (Grand Concert) revolves around Promo Concerts , which are held every 6 months starting in Late
> December of Junior Year, culminating in a Grand Concert in Senior Year. To succeed in these events, your
> trainee must gain and spend Performance Points to learn Songs and Techniques .
> Performance Points are gained by training with your Support Umamusume . Friendship Training is a priority as
> it grants more Performance Points.
> Techniques have a Mastery Bonus that grants instant stat boosts , skill hints , skill points , or energy
> recovery . Completing a set number of Techniques unlocks Songs, which provide two types of bonuses:
> Mastery Bonus — Grants flat stat boosts , skill points , or improves training gains upon learning.
> Mastery Bonus — Takes effect after concerts, providing bonuses like Friendship Training Effectiveness ,
> Specialty Priority , and Support Chain Event Frequency .

The page prints both song bonus tiers under the same heading, "Mastery Bonus"; GameTora names the second one
Live Bonus and the first one Practice Bonus (see its extraction below). The duplicate heading is the page's own
and is kept as found.

Caps, as printed (1200 → raised): Speed 1600, Stamina 1300, Power 1300, Guts 1500, Wit 1300. "Training stats
beyond 1200 are observed to have halved effectiveness."

Song unlock patterns: "In Year 1, songs require 1, 2, 3, and 4 techniques respectively. In Years 2 and 3, songs
require 2 techniques for the first 3 songs, and 4 techniques for the 4th song." Year 1 printed as (1-2-3-4-4),
Years 2 and 3 as (2-2-2-4-5). "Techniques grant an immediate bonus and can be learned at any point during your
career without ending your turn." "After learning your fourth song each half-year, it's highly recommended to
stop learning techniques and save your Performance Points. Pushing for a fifth song will drain your Performance
Points quickly, and the scaling of song unlocks resets after every concert."

Hype and the lives:

> Ensure each Promo Concert ends in a Great Success by maxing out the Hype Level before the performance. Each
> Promo Concert grants a stat increase , and achieving a Great Success boosts these gained stats even further.
> You can increase your Hype Level by learning songs throughout the scenario.
> If you've managed to grab 18 songs you get Girls' Legend U, the Hype Level will display Special Hype! instead.

Objective thresholds: 16 songs by Early November of Senior Year triggers the scenario-character skill-choice
event; 18 songs before Early December of Senior Year gives the rare scenario skill, and fewer than 18 gives its
common version; "Girls' Legend U does not count if it's your 18th song"; "Make Debut!", auto-unlocked after 4
turns from the start, does count. Also printed: "Fan Count is not a strict unlock condition" and "Not all
concerts need to be a huge success", with the caveat "Unlock conditions are based on JP experience."

Scenario event table, as printed. Game8 names the event **Closer Together**, Senior Year, Early November,
condition "Learn 16 Songs before Early November of Senior Year", five choices:

| Lyric line offered | Rare hint | Common hint if the character is neither trainee nor support |
|---|---|---|
| "A song with some call-and-response"... | Full Speed! hint lvl +1 | Full Tilt +1 |
| "Gratitude towards the fans, without whom I would not be running"... | Concentration hint lvl +1 | Focus +1 |
| "I'm home"... | Trackblazer hint lvl +1 | Rosy Outlook +1 |
| "The power to achieve a breakthrough"... | Come What May hint lvl +1 | All I've Got +1 |
| "Song brings us closer together"... | Lane Legerdemain hint lvl +1 | not printed |

Each rare name above was checked against the committed Global skill export (`skills.609afe88.json`) rather than
taken on faith: Full Speed! 202281, Full Tilt 202282, Concentration 200431, Focus 200432, Trackblazer 200711,
Rosy Outlook 200712, Come What May 201701, All I've Got 201702, Lane Legerdemain 200501. The export prints the
scenario skill as **I Wanna Win with You** (210071, rarity 2), lowercase `with`; Game8 prints "I Wanna Win With
You". "Trackblazer" in that table is a *skill name* (切り開く者), not the scenario, and the two now collide in one
document.

Scenario Spark: "Grand Concert has its own scenario-based Spark called **Our Grand Concert** . This spark boosts
Speed and Guts if it activates during inspiration." This matches the committed export row exactly
(`static_scenarios.json`, order 4, factor id 3000401, `effect_1` speed, `effect_2` guts, `name_en` "Our Grand
Concert", `name_ja` グランドライブシナリオ, `did_not_exist` pre_gl). GameTora's prose calls the same spark
"Grand Live Scenario", which is its rendering of the JP name.

Deck shape, as published: "Sets of 3 Speed, 2 Wit, and 1 Pal, or 2 Speed, 2 Wit, 1 Pal, and 1 open slot"; 1
Stamina type for Medium and Long. Light Hello `[From the Ground Up]` Pal SSR is called the must-have (chance for
free Performance Points, "You can get 20 Points for the type where you have the fewest points banked", Energy
Cost Reduction inside Friendship Training, 10% Training Effectiveness at 2 limit breaks, Initial Speed from 3
breaks, and the skill See Ya Later!, "the gold version of Playtime's Over!"). Both skill names resolve in the
export: See Ya Later! 201662, Playtime's Over! 201661. Priority songs: "Run for Our Dream!" and "Grow Up and
Shine!" for their Skill Point bonuses, both available in Year 2.

Song list, as Game8 prints the English titles (23 rows, with the effect pair and the cost figures exactly as
they appear on the page; the token type each cost belongs to is not printed as text):

| Year block | Song | Effect pair | Cost figures as printed |
|---|---|---|---|
| Junior | Believe in Miracles! | Training Wit Gain +1 / Specialty Priority +5 | 21, 21 |
| Junior | Full Speed Ahead! Umadol Power☆ | Speed +22 / Friendship Training Effectiveness +5% | 32, 12 |
| Junior | Getaway! Fallin' Love | Training Guts Gain +1 / Support Chain Event Frequency Lvl +1 | 21, 21 |
| Junior | Go This Way | Training Power Gain +1 / Support Chain Event Frequency Lvl +1 | 21, 21 |
| Junior | Here Comes Our Time | Power +22 / Friendship Training Effectiveness +5% | 32, 12 |
| Junior | Ring Ring Diary | Training Stamina Gain +1 / Support Chain Event Frequency Lvl +1 | 21, 21 |
| Junior | Run n' Run! | Skill Points +22 / Friendship Training Effectiveness +5% | 14, 16, 14 |
| Junior | Zero Is Where the Center Stands! | Training Speed Gain +1 / Support Chain Event Frequency Lvl +1 | 21, 21 |
| Junior | Make Debut! | All Performance Points +10 / Specialty Priority +5 | after 4 turns from start |
| Classic 1st half | Hey, Guess What! | Training Guts Gain +2 / Specialty Priority +5 | 42, 21 |
| Classic 1st half | Our Blue Bird Days | Training Speed Gain +2 / Specialty Priority +5 | 21, 42 |
| Classic 1st half | Run for Our Dream! | Training Skill Point Bonus +2 / Specialty Priority +5 | 21, 21 |
| Classic 2nd half | Grow Up and Shine! | Training Skill Point Bonus +3 / Support Chain Event Frequency Lvl +1 | 21, 21, 21 |
| Classic 2nd half | Hoppity Sunny Days ♪ | Training Stamina Gain +2 / Specialty Priority +5 | 42, 21 |
| Classic 2nd half | Seven Colors Scenery | Training Power Gain +2 / Specialty Priority +5 | 21, 42 |
| Classic 2nd half | Sunbeam Cheer | Training Wit Gain +2 / Support Chain Event Frequency Lvl +1 | 42, 21 |
| Senior | Dream Sky | Wit +22 / Friendship Training Effectiveness +5% | 22, 22 |
| Senior | Fanfare for the Future! | Guts +26 / Friendship Training Effectiveness +10% | 26, 42 |
| Senior | Precious Treasure Box | Speed +26 / Friendship Training Effectiveness +10% | 42, 26 |
| Senior | Present March ♪ | Power +22 / Friendship Training Effectiveness +5% | 22, 22 |
| Senior | Sky-Blue Spring | Guts +22 / Friendship Training Effectiveness +5% | 12, 32 |
| Senior | The World's at Our Whim | Stamina +22 / Friendship Training Effectiveness +5% | 32, 12 |
| Senior | Girls' Legend U | All stats +10 / Friendship Training Effectiveness +10% | Grand Concert song, not a lesson |

Release section: "Released on July 22, 2026, 10PM (UTC)"; "This is a permanent scenario and will remain
accessible even after additional scenarios are released"; released alongside the Light Hello Pal SSR, the Agnes
Tachyon Speed SSR and the Smart Falcon Alternative Outfit; "Grand Live is expected to be the main training
scenario for four (4) months until the release of the Grandmasters scenario in the future." `[JP]` dates on the
same page: Grand Live 2022-08-24, Trackblazer (MANT) 2022-02-24, Grandmasters 2023-02-24.

### GameTora our-grand-concert, verbatim extraction

GameTora's own header: "By robflop & Gertas, Last updated on 2026-07-22." Its section list is Basic Information,
Scenario Link and Character-specific Events, Story, Grand Live Mechanics, Inheritance, Training, Example Turns,
Lessons, Lesson Patterns, Promotional Lives and Grand Live, Song Lessons (split by availability), Skills, Base
Training Values, Unique Skill Level-ups, Training Facility Levels, Scenario Factor, Stat Caps.

> Brighter Together: Our Grand Concert (also called Grand Live outside of Global) is the fourth training
> scenario to be added to the Uma Musume game. It was released on July 22, 2026 on Global and August 24th, 2022
> on JP.

> This scenario marks the return of the Scenario Link mechanic, featuring Silence Suzuka, Agnes Tachyon, Smart
> Falcon, Mihono Bourbon, and Light Hello, a new original NPC introduced in the story of Grand Live.
> Character-specific secret events (e.g. Runaway events) are also back.

Story: the scenario is about reviving the titular Grand Live, "a big fan appreciation festival that was once
regularly held in the past", and Light Hello (ライトハロー) is "an event producer" voiced by Kana Ueda.

Mechanics:

> Before being able to revive the Grand Live and turn it into a great success (大成功), you must successfully
> carry out a set of four Promotional Lives, held every six months starting from late December of the first
> year. To achieve this, you will have to raise the "Hype Level" (ライブ期待度) gauge of each live by using the
> new Lesson mechanic.

Inheritance raises the caps, which no other `[Global]` scenario entry in this master claims:

> In Grand Live, it will also raise your stat caps (both at the start of the run and during inheritance
> events). The stat uncaps received at the start of the run are based on the blue factors of the parents. A
> one-star blue factor will uncap its corresponding stat by 4, a two-star factor by 9, and a three-star factor
> by 16. In total, you can get a maximum of 48 stat points uncapped with one 9\* parent.
> Unlike previously, unique skill (green) factors triggering during inheritance events will now also give stat
> uncaps (but no flat stats) besides the skill hint. Which uncaps they give will depend on the stat growth
> bonuses of the character the unique skill is from. For example, Vodka's Cutting × DRIVE! factor will give
> Speed and Power uncaps, as she has a 10% Speed and 20% Power bonus.
> The value of the uncaps received during inheritance events from blue and green factors seems to be randomized
> within a range of values, similar to flat stat gains. The exact ranges are yet unknown.

The resource, named as tokens:

> In Grand Live, apart from the basic stats (Speed, Stamina, Power, Guts, Wisdom), you will also gather a new
> set of performance tokens called **Dance, Passion, Vocal, Visual, and Mental**. These tokens are initially
> capped at 200 each and can be obtained in training alongside stats.
> While there is no set correspondence of which training facility will give which performance token, each has a
> primary and secondary token that they are more likely to provide you with.

> Based on testing done so far, an estimate is that a facility will give you tokens of its primary type around
> 60% of the time, tokens of its secondary type around 30% of the time, and tokens of any other type as the
> remaining 10% of the time. This distribution is random for each turn.

GameTora's facility-to-token table has no text cells: each row carries two icon images from
`/images/umamusume/icons/perf_tokens/`, and the mapping below is read off those icon file names, which is the
only place this correspondence exists in this extraction. **Method stated because it is an inference from asset keys,
not from prose:** Speed → Dance primary, Visual secondary; Stamina → Passion primary, Vocal secondary; Power →
Vocal primary, Mental secondary; Guts → Visual primary, Dance secondary; Wit → Mental primary, Passion
secondary. A client capture is what would confirm it.

Lessons:

> Lessons are a new mechanic unique to Grand Live, with which you can spend your performance tokens to learn
> Live Techniques or practice new songs to play in Promotion Lives. Song lessons (楽曲) will have their cover on
> the left, whereas Live Technique lessons (ライブテクニック) will display various icons related to stats and the
> like. The list of available lessons will stay static until you decide to complete one of the three. Once this
> happens, the list will refresh, allowing you to do multiple lessons in a single training turn.
> The button to access the lesson menu is located between the outing and race buttons of the home menu. It will
> be locked until the fifth turn of the training run, which is when the 「グランドライブ再建計画、開始！」
> training event happens, signaling the start of the Grand Live mechanics.
> Practicing songs in lessons will raise the Hype Level gauge... Three songs are required to fill the Hype
> Level gauge fully. Automatically gained songs also count for this.
> When you lack performance tokens for a lesson, the green "confirm" button (習得) will change to a "reserve"
> button (予約). Once you have reserved a lesson, the game will display 「あとX」 above the token indicator on the
> training menu to indicate a lack of X performance tokens of that type.
> In addition to gaining Live Techniques or practicing songs, lessons also have additional Practice Bonuses
> (習得ボーナス, two yellow up arrows) that will be awarded alongside the learned technique or practiced song...
> These can range from stats (such as giving you 10 Speed) to skill hints or skill points.
> Aside from that, song lessons also grant Live Bonuses (ライブボーナス, purple microphone below practice bonus),
> whose effects include, for example, increasing the likelihood of triggering support card chain events
> (サポート連続イベント率アップ). These will, however, not immediately go into effect like Practice Bonuses, but
> rather be "queued" up for activation after the next live happens... Live Bonuses will stay in effect for the
> entirety of the training run and have levels that can be raised by doing lessons with the same bonus.
> You can also do lessons before character objectives alongside learning skills.

> The Grand Live scenario features a total of three Live Bonuses: Friendship Bonus (友情ボーナス), Speciality
> Rate Up (得意率アップ), Support Event Chance Up (サポート連続イベント率アップ).

Lesson patterns, as GameTora's table prints them (initial then looping): before the 1st Promo Live 1-2-3 then
4-4-2-2; before the 2nd, 3rd and 4th 2-2-2 then 4-5-2-2; before the Grand Live 2-2-2 then 4-3-2-2. "The pattern
progress will reset completely after a Live... it's possible to reach the selection of a song lesson before
doing a Promotional Live and have it carry over to the next segment. A song carried over this way and learned
after the Live will count as one point for the following initial pattern, saving you one lesson."

Promotional Lives and the Grand Live:

> Depending on the outcome of a Promotional Live, you may gain more collaborators for the Grand Live. Making a
> Promotional Live a "Great Success" (大成功) by sufficiently raising the Hype Level beforehand will raise your
> stat caps.
> Lives will additionally award you 5 Skill Points for every technique lesson and 25 Skill Points for every song
> lesson you have taken since the previous Live. They will also raise the cap of performance tokens by 50 when
> successful (no matter if you achieve great or normal success). A fully filled Hype Level gauge will always
> guarantee a Great Success.
> You may choose to do lessons before stepping onto the stage of a Promotional Live or the Grand Live like you
> would learn skills before races.
> Learning at least 18 songs (excluding "GIRLS' LEGEND U") before late December of the Senior year will make a
> special version of "GIRLS' LEGEND U" play in the Grand Live. It will also be available in the Live Theater
> afterward. With 17 or fewer songs, you will unlock a normal version of "GIRLS' LEGEND U" instead.

Songs and totals: "You won't get any new lesson songs after the 3rd Promotional Live, so including the
automatically gained specials (Make Debut! and GIRLS' LEGEND U), there's a total of 23 songs." Its all-songs
cost row prints five figures, 252 / 201 / 150 / 275 / 196, one per token type, and the page does not print the
column labels as text, so which figure belongs to which type is **not** extracted here. GameTora's per-song rows
carry the JP titles in romaji (Kiseki wo Shinjite!, Tachiichi zero-ban! Juni wa Ichiban!, Nigekiri! Fallin'
Love, Seishun ga Matteru, RUN×RUN!, Zensoku! Zenshin! Umadol Power☆, Yume wo Kakeru!, A・NO・NE, Bokura no
Bluebird Days, Komorebi no Yell, Pyoitto ♪ Hallelujah!, Nanairo no Keshiki, Yumezora, PRESENT MARCH♪, Daisuki no
Takarabako, Sekai wa Bokura no Iinari Sa, Harusora BLUE, Fanfare for Future!, Grow Up, Shine!) and the same
effect-plus-cost pairs Game8 prints in English.

Skills: "Learning 18–21 songs (excluding GIRLS' LEGEND U) before late December of the Senior year awards a Lv 1
hint for the skill. Learning all 22 songs upgrades this reward to Lv 3 hint. With 17 or fewer songs, you receive
a Lv 1 hint for the skill instead." Which skill each band awards sits in an image table, so the gold-and-common
split is taken from Game8's prose, not from this page. The same section states the Early November event
「あなたと私を繋げるライブ」 fires at 16 or more songs, offers five lyric lines, four tied one-to-one to a
scenario link character and the fifth "an unrelated standard choice".

Base training values at facility level 1, no supports, no character growth: Speed +8 Speed, +4 Power, +4 Skill
Points, 10 tokens, −19 energy; Stamina +8 Stamina, +6 Guts, +4 SP, 10, −20; Power +4 Stamina, +9 Power, +4 SP,
10, −20; Guts +2 Speed, +2 Power, +7 Guts, +4 SP, 10, −20; Wit +2 Speed, +6 Wit, +5 SP, one further figure
printed as +5 whose column is not recoverable from the text layer (the three-column header is Stat gains / Token
gain / Energy and only two values follow, so this row is recorded as **partially extracted**).

Unique skill level-ups: "The mechanic for leveling up unique skills in Grand Live is equal to that of the URA
Finals scenario. That means getting 60.000 fans by Valentine's Day (Early February), 70.000 fans by Early April,
and 120.000 fans by Christmas (Late December) of the Senior year (the third year). These values are 40.000,
60.000, and 80.000, respectively, for characters with high dirt aptitude but low turf aptitude (such as Haru
Urara or Smart Falcon). The April level-up also requires you to have a green bond gauge (3 bars) with chairman
Akikawa."

Facility levels: "Just as in the URA Finals scenario, the level of the training facilities in Grand Live will
rise depending on how often you train at it. All facilities start at level 1 and level up every four times you
use them... until level 5."

Stat caps: Speed 1600, Stamina 1300, Power 1300, Guts 1500, Wit 1300 (the page prints the fifth stat under its
own gloss for 賢さ; the client word is Wit and conflict row 4 in `docs/UMAMUSUME_REFERENCE.md` §7 is the standing
ruling on that alternation).

### `[Global]` official notice 899, tier S

Posted 2026-07-22 22:00 (UTC), titled "New Career Scenario Celebration Pretty Derby and Support Card Scouts out
now!". Body, in its own words:

> As of 10:00 p.m., Jul 22, 2026 (UTC), a New Career Scenario Celebration Pretty Derby Scout and New Career
> Scenario Celebration Support Card Scout have begun! ... The Trainee Umamusume and Support Cards debuting in
> these Scouts are featured as Scenario Link characters in the Career Scenario "Brighter Together! Our Grand
> Concert." ... New Career Scenario Celebration Scout Availability Period: 10:00 p.m., Jul 22 - 9:59 p.m., Aug
> 10, 2026 (UTC).
> Trainee Umamusume ■ Debut Trainee Umamusume (Spotlight): ★★★ [Twilight Triumph] Smart Falcon.
> Support Cards ■ Debut Support Cards (Spotlight): • SSR [From the Ground Up] Light Hello • SSR [Q≠0] Agnes
> Tachyon • R [Event Producer] Light Hello.

The notice also uses "Pal type" for the support-card type ("including some of the Pal type"), which is a tier-S
confirmation of the `[Global]` label the terminology map already carries and of `lang/en/uma.php` `card_pal`.
The scenario name inside the notice is printed with an exclamation mark, "Brighter Together! Our Grand Concert",
while the data export's `name_en` and every repository document print it without one. Recorded as conflict row 52
in §7. **Resolved the same day by a second official page:** notice 905 routes the player to "Brighter Together!
Our Grand Concert under Career in Help", and a Help path is a client string rather than sentence punctuation, so the
exclamation mark belongs to the title and the export field is the outlier.

### `[Global]` official notice 905, tier S: the scenario in Cygames' own English

Fetched 2026-10-05 from the official news API (`POST https://umamusume.com/api/ajax/pr_info_detail?format=json`, body
`{"announce_id":905}`; the `message` field carries the whole body, verified against the rendered page). Posted
2026-07-22 22:00 UTC. Verbatim, with the notice's own sub-headings:

> As of 10:00 p.m., Jul 22, 2026 (UTC), the new Career scenario "Brighter Together! Our Grand Concert" has been added
> to the game! In this new scenario, you can train your trainees using a different system than all previous Career
> scenarios. ... Check out the newest Career scenario, featuring new characters and new systems!
>
> **Grand Concert.** In this new Career scenario, your goal is to revive the Grand Concert. Four Promo Concerts and one
> Grand Concert are held, making for a total of five concerts. Work towards making things a great success by making
> each concert a spectacle! Concerts are held biannually starting from the second half of December during your
> trainee's junior year. Prepare for concerts during the period leading up to them and make them unforgettable!
>
> **Performance and Lessons.** In this new Career scenario, you acquire Performance through activities like training
> and Career events. There are five types of Performance: Dance, Passion, Vocals, Visuals, and Composure. By spending
> Performance in lessons, you can acquire new songs and concert techniques. Songs and concert techniques don't just
> make concerts more exciting; they also have various effects, such as increasing your parameters, recovering energy,
> and boosting training effects.
>
> **Live Performance Expectations.** By holding lessons and acquiring new songs as you work towards your next concert,
> your Live Performance Expectations will increase. When it reaches its max, your next concert will be a Great
> Success. Make sure to acquire plenty of songs and make your concert a Great Success!
>
> Important Information 1. For more information on the new Career scenario, please refer to Brighter Together! Our
> Grand Concert under Career in Help.

**What that settles on `[S]` copy:** the resource noun is bare **Performance**, with no Point or Token after it; its
five types are **Dance, Passion, Vocals, Visuals, Composure**, which is Game8's list exactly and corrects GameTora's
fifth; the events are **Promo Concert** and **Grand Concert**, four plus one; the cadence is biannual from the second
half of December of Junior Year; the purchases are **lessons** yielding **songs** and **concert techniques**; the
effects "increase your parameters, recover energy, and boost training effects"; the gauge is **Live Performance
Expectations** and its ceiling yields a **Great Success**; and the title carries the exclamation mark, inside a Help
path.

**What it does not.** It names neither bonus layer, so row 51 stands open between the two guides. It prints no Song
titles, so row 49 stands open. It carries **no number at all**, so the 200 cap and the +50 per concert, the lesson
patterns, the 5 and 25 skill-point payouts, the three-songs-to-full gauge, both song thresholds and every cost figure
still rest on tier-A and tier-B pages, and the turn-five gate, the reserve button and the inheritance cap-raise are
guide-only claims. The manifest's 0 captured frames is unchanged, and it is still what rows 49 and 51 are waiting on:
this is official prose, not a screen.

### What this read did not settle

- **No client frame exists**, and it still earns its place: `docs/research-scratch/DESIGN-CORPUS.md` records 0 captured frames for this
  scenario, so no Global client string above is measured. The resource noun (Point versus Token), the fifth
  type's word (Composure versus Mental), the event name (Closer Together versus 「あなたと私を繋げるライブ」) and
  the song titles' language are third-party renderings.
- **Nothing that GameTora renders as an image was extracted as a number.** The facility-to-token table, the
  per-column song cost totals, and the gold-versus-common skill column in the Skills table are the three cases,
  each stated above with the method used or the reason it failed.
- **No per-level magnitude tables exist on either page.** No Lesson cost-per-level curve, no Hype-point value per
  song, no Live Bonus percentage ladder.
- The 2026-09-27 「Fully Charged」 reading is **refuted by the page it came from**; see §7 conflict 4 and
  `docs/UMAMUSUME_REFERENCE.md` §2.8.
