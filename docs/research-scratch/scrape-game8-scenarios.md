# Game8 Scenario Extraction (Global-relevant scenarios)

Source pages read with a real rendering browser (Playwright). All numbers below appear on the rendered page; nothing is reconstructed from memory or from another game.

Server note that applies to all three sections: each page carries a Global banner statement. The URA and Unity Cup pages both state that their scenario updates "were made on July 1, 2026 in the Global version". The Trackblazer page states the scenario is live Global as of March 12, 2026. JP-only facts appear only inside the per-page "Japan Scenario Release Dates" tables; those are tagged `[JP]` and are release history, not availability.

Scenario count check from all three pages (identical footer table "List of All Career Scenarios" on each page): URA Finale Scenario, Unity Cup Scenario, Trackblazer Scenario, Grand Concert (Grand Live) Scenario. That is the four-scenario Global list this extraction was scoped against.

---

## URA Finale (https://game8.co/games/Umamusume-Pretty-Derby/archives/536520, last updated July 6, 2026 06:18 AM)

Article title as printed: "URA Finale Scenario Guide". Page banner: "ⓘ Updates to the URA Finale scenario were made on July 1, 2026 in the Global version of Umamusume, ahead of its original anticipated release."

### 1. Special rule this scenario adds

URA Finale is described as the base career: "the first career available in the Global version", "the most straightforward out of all" career modes. Its own resource layer is the rework added on July 1, 2026 in Global, which introduces a duel partner and a duel reward track that the pre-rework scenario did not have:

- Happy Meek appears on training after the new scenario event "I'm Here to Challenge You". She can appear on any stat. She does not give Friendship trainings, unlike Team Sirius (Passing the Dream On) and Heirs to the Throne (Esteemed and Adored). `[Global]`
- Dueling Happy Meek is the new action layer: you pick one of three options (two random stats plus the stat you dueled her on), each option carries an icon showing your chance of winning that option. `[Global]`
- Duel wins raise a "dueling level" with Happy Meek; at 6 duel wins she becomes a powered-up opponent in the URA Finals. `[Global]`
- Scenario Link character: Aoi Kiryuin "will occasionally appear during your career that will benefit your stats during her events with Happy Meek". `[Both]`
- Stat caps are scenario-specific and were raised by the rework (+200 to each of the five stats). `[Global]`

### 2. Phase structure

- Class/year sequence in the fixed event calendar: Junior Year, Classic Year, Senior Year (three tabs, numbered 1, 2, 3). `[Both]`
- Junior: Inspiration at Start; "Road to Stardom" after 3 turns, pre-debut (Director Akikawa starts appearing in training); debut race "[Race] Junior Make Debut" after 11 turns; "After the Debut" gives Promotion to Beginner Class and unlocks running other races; "A Quirky Respondent?" Early July (last turn) puts Reporter Etsuko Otonashi into training. `[Both]`
- Classic and Senior each contain a 4-turn Summer Camp block (Early July, Late July, Early August, Late August). `[Both]`
- No extra race objective layer beyond the trainee's own race goals plus fan-count events; the final stage is the URA Finale three-race block. `[Both]`
- Final-stage condition chain, printed as three separate calendar rows with win-gated triggers: "After the URA Finale Qualifier" (trigger: Winning the URA Finale Qualifier), "After the URA Finale Semifinals" (Winning the URA Finale Semifinals), "After the URA Finale Finals" (Winning the URA Finale Finals). All three are marked "Affected by Race Bonus!". `[Both]`
- Career-end gates: "Individual Trainee Event" triggers on Winning the URA Finale Finals and gives the trainee's Good Ending. "A Super Successful Event!" triggers on Winning the URA Finale Finals plus Max Friendship with Director Akikawa. `[Both]`

### 3. Numeric effects as printed

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

### 4. Training-relevant decision the scenario forces

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

### 5. Event structure as presented

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

## Unity Cup (https://game8.co/games/Umamusume-Pretty-Derby/archives/545572, last updated July 7, 2026 02:39 AM)

Article title as printed: "Unity Cup Update Guide". Full scenario name as printed: "Unity Cup: Shine On, Team Spirit! (Aoharu Hai) is the updated second permanent scenario". Page banner: "ⓘ Updates to the Unity Cup were made on July 1, 2026 in the Global version of Umamusume: Pretty Derby, ahead of its original anticipated release in November 2026."

### 1. Special rule this scenario adds

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

### 2. Phase structure

- Years are the usual Junior / Classic / Senior. The scenario's own phase layer is five team-race rounds, printed as "Unity Cup Team Race Schedules": `[Both]`
  - Round 1: After Junior Year Late Dec
  - Round 2: After Classic Year Late June
  - Round 3: After Classic Year Late Dec
  - Round 4: After Senior Year Late June
  - Finals: After Senior Year Late Dec
- "Every six months in your career (Late June and Late December), you will have to participate in Team Races which has you fielding five teams of racers similar to Team Trials." Each team race is 5 races, one per distance (Sprint, Mile, Medium, Long, Dirt); clearing all five gives an increase to all stats sized by your performance. `[Both]`
- Round 4 is the phase where the extra opponent tier lives: Elite Team can appear, and beating it exposes S+ Team Zenith ("S+ Team Zenith can be encountered when you beat the Elite Team in the 4th round of the Unity Cup"). `[Global]`
- Final-stage condition: win against Team Zenith in the Unity Cup finals to receive your chosen Team Name's skill. Trainee race goals are unchanged and still govern run survival: "you will still need to do well in your trainee's race goals, including the final 3 URA Finale races. Similar to the URA Finale, failing your trainee's race goals enough times will lead to your career run ending." `[Both]`

### 3. Numeric effects as printed

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

### 4. Training-relevant decision the scenario forces

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

### 5. Event structure as presented

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

## Trackblazer (https://game8.co/games/Umamusume-Pretty-Derby/archives/580723, last updated August 25, 2026 02:16 AM)

Article title as printed: "Trackblazer (Make a New Track) Scenario Guide". Full name as printed: "Trackblazer: Start of the Climax (Make a New Track/MANT) is the third permanent scenario". Sibling guides named on the page (not read, per scope): Trackblazer Race Schedules, Trackblazer Items and Tier List, Trackblazer Epithets, All Trackblazer Events and Choices, How to Unlock Long TS Climax.

### 1. Special rule this scenario adds

Two systems the base scenarios do not have, printed as the scenario's definition: `[Both]`

- Shop Coins and a Pro Shop. "Trackblazer lets you collect Shop Coins that you can use to buy items in the Pro Shop that will boost your performance within the current run of this career mode." Items "vary from improving your stats to giving you a certain condition, energy, support card bonds, and much more." `[Both]`
- Result Points replace race goals. "Unlike the URA Finale and Unity Cup, Trackblazer has no race goals and no Scenario Link. Every Umamusume will need to get enough Result Points by Late December of each year instead. Excess Result Points are not carried over to the following year." `[Both]`
- Rival races: flagged by the VS icon on the Race button, and only for distances at C aptitude or better; beating a Rival yields skill hints. `[Both]`
- Epithets/Titles as a cross-career achievement layer granting hints. `[Both]`
- Trainee secret events do not exist here: "Secret events of Umamusume don't trigger in the Trackblazer scenario... For example, Silence Suzuka cannot get the Runaway style in this career as it is tied to her secret event." `[Both]`
- The scenario's own summary line: "Trackblazer offers a more open career with no race goal restrictions, making the player focus their training strategy around Races and the Pro Shop to achieve the best results. This scenario does not have secret events." `[Both]`

### 2. Phase structure

- Same Junior / Classic / Senior year frame, printed as three rotation columns; the guide's sample is a "Turf, Mile to Medium race rotation". `[Both]`
- No per-year turn quota is printed; instead the whole-career budget is stated: "Counting the trainee's debut but not the last turns for the Twinkle Star Climax, there are about 72 turns where the expected schedule consists of around 30 races or more. That's nearly half of all turns, and double or triple compared to previous scenarios, where 10-15 races are normally done." `[Both]`
- Extra objective layer: a Result Points checkpoint at Late December of each year, with year-end placement tiers, plus the Umamusume of the Year award on top of it. `[Both]`
- Rival appearances and Daily Sales shop access grow with racing: "You usually buy Alarm Clocks from the Daily Sales shop, which becomes available as you participate in daily races." `[Both]`
- Final-stage condition: "The last three races of the scenario are replaced by the Twinkle Star Climax. For these races, you need to earn enough Victory Points across all three to place first. This means it's possible to still get 1st overall even if you don't win all 3 races." `[Both]`

### 3. Numeric effects as printed

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

### 4. Training-relevant decision the scenario forces

- The scenario inverts the usual priority: "Unlike the URA Finale where training itself is important and the Unity Cup where Unity Training is the highest priority, winning Races is the key to developing a trainee into a strong, competitive Umamusume the Trackblazer Scenario." `[Both]`
- Training level is earned the base way: "Leveling up facilities in Trackblazer works just like the URA Finale, where they increase in level the more you use them. As such, it's recommended to have at least 2 of the types you want in your deck to stack Friendship training." `[Both]`
- Training Applications: "special items that raise a training facility's level and can also be bought with coins. Only buy these if you can afford them, since most stat gains from training are expected during the Summer, where all facilities are fixed at Level 5." `[Both]`
- Skip-a-race decision, printed with its condition: "you can also choose to forego racing in a G2 or G3 race if you have Friendship Training on a stat that you want to improve (like Speed or Wit). Although you will lose out on Shop Coins, the stats you get from training can outweigh what you can get from winning the race. This is especially true if your scheduled G2 or G3 race does not have a Rival in it." `[Both]`
- The failure spiral is the reason item stock matters: "Lose Race → Less Shop Currency (→ Need More Currency? Do More Races) → Less Items Over Time → Less Training with Items → Less Overall Trainee Progression." `[Both]`
- Recommended allocation is stated through deck archetypes rather than target stat values: 2 Wit in every deck, 2 Speed in most regular decks, Race Bonus at 50%+ minimum, win 30+ races, and the three support effects ranked as printed (Race Bonus ★★★, Initial Stat Bonuses ★★, Initial Friendship ★). `[Both]`
- Named recommended stat caps/skirts: raise "at least" the Mile-Medium-Long, Mile-Medium, or Medium-Long Turf aptitude coverage to A, so Rival races can appear; "Having B-aptitude can be acceptable, but A is the optimal grade for a higher chance of winning." `[Both]`
- Wide-aptitude trainees favored as printed: Agnes Digital (Full-Color Fangirling), Taiki Shuttle (Wild Frontier), Oguri Cap (Starlight Beat), El Condor Pasa (El Numero 1). Narrow-pool trainees harder here: Manhattan Cafe (Creeping Shadow), Meisho Doto named in prose; Smart Falcon (LOVE☆4EVER) listed as Dirt-specialized and disadvantaged because "Dirt races at distances of Mile and above are rare in Junior Year". `[Both]`
- F2P note as printed: "The most important stat in Trackblazer is Race Bonus, and building a strong deck using free cards is fairly easy." `[Both]`

### 5. Event structure as presented

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

## Terminology Seen

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

## Read Failures

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

## Disagreements

1. Scope of the 0% failure rate from a purple Spirit Burst. The URA page (July 6, 2026) writes that Unity Cup's version "set[s] failure rate to 0% whenever it appears on any training", while the Unity Cup page (July 7, 2026) writes "There is a 0% Failure Rate in the training facility where the Support has active ESB". One facility versus any training is a real difference in play. Both statements listed; no resolution attempted.
2. Name of that same mechanic. The URA page (July 6, 2026) calls it "Enhanced Spirit Bursts"; the Unity Cup page (July 7, 2026) calls it "Extreme Spirit Bursts (ESB)" and is the page that renders the in-game tooltip wording. Recorded as two published English strings for one July 1, 2026 mechanic; I did not decide which is the client term.
3. Skill-point output per scenario. The Unity Cup page (July 7, 2026) states the updated Unity Cup yields "2,000-2,500+ Skill Points" against "Trackblazer's average of 2,800". The Trackblazer page (August 25, 2026) prints no skill-point average at all, so the comparison has only one side published. Flagged rather than reconciled.
4. Stat cap figures and the export. The pages print 1400 per stat for URA Finale, 1300 per stat plus 1800 Wit for Unity Cup, and no cap figure for Trackblazer, all described as post-July 1, 2026 scenario caps with a 1200 base. These are the guide-stated scenario cap values and are not the same quantity as the `hard_caps` arrays in your export, which I did not have on any page. I am not claiming the two agree or disagree; I am flagging that the numbers I read cannot be matched one-to-one against that field without a definition of what each measures.
5. Scenario ordering, minor. The Unity Cup page's "Next" cell dates Trackblazer (MANT) at February 24, 2022 in JP, and its own Japan Scenario Release Dates block lists Aoharu Hai at August 30, 2021 with a "(6 Months)" duration. The Trackblazer page repeats both. No conflict found on dates; noted only because the "(6 Months)" label appears on one page and not the other with no explanation of what it measures.
