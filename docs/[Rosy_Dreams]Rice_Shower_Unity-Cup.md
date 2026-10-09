# Unity Cup run report: [Rosy Dreams] Rice Shower

**Evidence base:** 68 `[Global]` client frames captured 2026-10-03: 19 at 16:09, 19 at 16:27, 20 at 17:07
and 10 at 17:53. One pair from the 17:07 set is byte-identical, so 67 distinct. Frame references are the
first eight characters of each capture filename. The deck's effect sets and skill lists come from a
second source: the seeded catalog behind `/support-cards`, rows 30003, 30016, 30018, 30019, 30020 and
30030. This revision supersedes the first-pass version of this file, which read only the 16:09 set.

**Research folded in:** the two online passes (Spirit Burst counting, cap composition) returned on
2026-10-03. Their results are in §1.2, §2.2, §2.4 and §6, with publisher dates and the repo's ⚠️ STALE
mark on anything older than 90 days.

---

## 1. What the screenshots establish

### 1.1 Run identity and moment

Scenario Unity Cup, confirmed by the logo panel "Unity Cup: Shine On, Team Spirit!" (`885d4671`).
Trainee Rice Shower, `[Rosy Dreams]` card, ★3, Potential Lvl 2 (`1dde6b2f`). Turn: Senior Year Early
Oct, **5 turns left, 5 turns until the Unity Cup** (`297b931b`). Fans 209,245, class Star; the ladder
puts Top Star at 240,000, so **30,755 fans** is the open numeric target (`7a68aedc`).

### 1.2 Live stat line and the caps above it

| Stat    | Value | Rank | Cap shown | Growth | Over the 1300 floor   |  |
| ------- | ----- | ---- | --------- | ------ | --------------------- |  |
| Speed   | 607   | B    | 1325      | +0%    | +25                   |  |
| Stamina | 577   | C+   | 1322      | +10%   | +22                   |  |
| Power   | 549   | C+   | 1368      | +0%    | +68                   |  |
| Guts    | 736   | B+   | 1308      | +20%   | +8                    |  |
| Wit     | 397   | D+   | 1800      | +0%    | N/A (different floor) |  |

Skill Points: **173 unspent** (`09e754d6`). The 1300 / 1300 / 1300 / 1300 / 1800 floor is the scenario
base: the 2026-07 rework added +100 to Speed, Stamina, Power and Guts and +600 to Wit ([game8.co
545572, 2026-07-07](https://game8.co/games/Umamusume-Pretty-Derby/archives/545572)), and above 1200 a
stat point buys less race performance than below it.

The four extras over that floor were attributed in the first version of this file to inheritance
breakthroughs, which run **★1 +4 / ★2 +9 / ★3 +16 per activation** at each of three inheritance moments
(career start, Classic April, Senior April; [gamewith, ⚠️ STALE: 2023-06-28](https://gamewith.jp/uma-musume/article/show/360428),
corroborated by [game8.jp, ⚠️ STALE: 2025-11-21](https://game8.jp/umamusume/475668)). **The ancestry
panels in §1.5 falsify that attribution.** The tree holds six blue sparks: Stamina ★★★, Power ★★★,
Speed ★★☆, Speed ★★☆, Guts ★☆☆, Power ★☆☆. Only the Guts figure survives, at +4 × 2 = 8. Speed +25 is
not a sum of 9s, Stamina +22 is not a sum of 16s, and Power +68 needs four activations of a ★★★ in a run
that has three inheritance events. At least one further cap layer is contributing and none of it is
visible in these frames.

The support-card layer is the obvious candidate and it is the one that does not hold up: ids 20 to 24
are real client Max-Stat effect slots under the Global names Max Speed through Max Wit
([umamusu.wiki support-effects module, 2026-09-17](https://umamusu.wiki/Module:Game/Supports/Data/Effects)),
but no publisher page in the research pass carries a per-level magnitude for any Global card, and the
seeded catalog's effect arrays for these six cards hold no Max-Stat entry at all. Bursts raising **team
members'** caps is documented with no magnitude
([kamigame, ⚠️ STALE: 2024-04-10](https://kamigame.jp/umamusume/page/172795448925364187.html)). The gap
stays open, now as a measured shortfall rather than an unexamined number.

Aptitudes (`1dde6b2f`): Turf A, Dirt G. Sprint E, Mile B, Medium A, Long A. Front A, Pace A, Late B,
End G. A Medium/Long build with the pace half available, and Guts is the designed carry stat at +20%.

*Dated erratum 2026-10-09 (D8 of the UX walk remediation). The sentence above stands as written, and
three of its letters do not agree with the tool's committed catalogue source. The GameTora export the
seeder reads (`database/seeders/data/gametora-characters.e9e9ee6d.json`, card `103001`, `[Rosy
Dreams]`) carries `aptitude: ["A","G","E","C","A","A","B","A","C","G"]` in the order turf, dirt,
sprint, mile, medium, long, front runner, pace chaser, late surger, end closer — Mile **C**, Front
Runner **B**, Late Surger **C**, where this document records B, A and B. Measured 2026-10-09: the
seeded column and the trainee picker both reproduce the export letter for letter, so no seed,
transformation or display defect exists on the tool's side; the two readings disagree about the
client. Which one the game actually prints was not settled here, and the tool was not changed: the
picker's values are the catalogue's own (`CareerTraineeSelectTest` pins all ten). A reader comparing
the two should treat the difference as open, not as a bug in the tool.*

### 1.3 Skills owned

The trainee's own Details screen, Skills tab (`1dde6b2f`), shows ten chips and the list scrolls:
Blue Rose Closer **Lvl 3** (her unique, gradient chip), Red Shift/LP1211-M, Corner Adept ○, Swinging
Maestro, Nimble Navigator, Steadfast, Deep Breaths, Extra Tank, Pace Chaser Straightaways ○, Shrewd
Step. The Learn screen additionally marks **Corner Recovery ○** as Obtained (`09e754d6`), so the set
runs past the ten visible chips.

### 1.4 Deck, all six cards, all SSR

Identity read from the six Featured Cards panels; the full effect set and the skill lists come from the
seeded catalog behind `/support-cards` (`support_cards` rows 30003, 30018, 30020, 30016, 30030, 30019),
which carries each card's effects at their maximum anchors. The client popup shows only what the card
has reached at its current level and limit breaks, so the two columns below are different things: the
catalog is what the card can be, the client column is what this run has.

| Card                                   | Type    | Client level | Unique Perk                                                             | Scenario link |  |
| -------------------------------------- | ------- | ------------ | ----------------------------------------------------------------------- | ------------- |  |
| Tokai Teio [Dream Big!]                | Speed   | 35/35        | Dream Big! L30: Friendship Bonus and Initial Speed                      | no            |  |
| Nishino Flower [Even the Littlest Bud] | Speed   | 30/30        | Even the Littlest Bud L30: Mood Effect and Initial Friendship Gauge     | no            |  |
| Biko Pegasus [Double Carrot Punch!]    | Speed   | 30/30        | Double Carrot Punch! L30: Training Effectiveness and Specialty Priority | no            |  |
| Super Creek [Piece of Mind]            | Stamina | 50/50        | Piece of Mind L30: Friendship Bonus and Specialty Priority              | no            |  |
| Matikanetannhauser [Just Keep Going]   | Guts    | 30/30        | Just Keep Going L40: Friendship Bonus and Initial Guts                  | no            |  |
| Haru Urara [Urara's Day Off!]          | Guts    | 35/35        | Urara's Day Off! L40: Friendship Bonus and Race Bonus                   | **yes**       |  |

Three Speed, one Stamina, two Guts, no Wit, no Pal. The sixth slot is a **friend card**: the Career
Profile rail tags Super Creek's entry with a "Friends" pill where the other five carry their discipline
icon (`85e4c75a`), which is why the only 50/50 card in the deck is the one borrowed from another
account. The epithet on Matikanetannhauser carries no
trailing exclamation mark in either the catalog or the client; the first pass of this file printed one.
The scenario-link column is the application's own derivation, `SupportCard::isScenarioLink('unity_cup')`
against `config('scenarios.php')`, and it confirms the corpus citation this file previously leaned on:
Haru Urara is the deck's only Unity Cup link, so every Special Training and Burst instance she joins
carries the +1 (`02-unity-cup.md:78`). Super Creek at 50/50 is the only maxed card, and every client row
visible for her equals her catalog maximum, which is the check that the two columns mean what they say.

| Card               | Effect set, values at max (catalog)                                                                                                                                   | Client rows visible                                                                                                                |  |
| ------------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------- |  |
| Tokai Teio         | Friendship Bonus 20, Mood Effect 60, Power Bonus 1, Initial Friendship Gauge 25, Race Bonus 10, Fan Bonus 15, Hint Levels 2, Hint Frequency 40, Specialty Priority 35 | FB 16, Mood 45, Power Bonus 1, Hint Freq 33, Specialty Priority 25, Hint Levels (row cut), Race 45 locked, Fan 45 locked           |  |
| Nishino Flower     | FB 20, Mood 30, Speed Bonus 1, Training Effectiveness 10, Initial Speed 30, Initial Friendship Gauge 35, Race 5, Fan 15, Hint Levels 3, Hint Frequency 50             | FB 15, Mood 20, Training Eff 5, Hint Levels Lv2, Hint Freq 40, Speed Bonus 35 locked, Initial Speed 45 locked                      |  |
| Biko Pegasus       | FB 25, Speed Bonus 1, Training Eff 15, Initial Speed 35, Initial Friendship Gauge 30, Race 10, Fan 20, Specialty Priority 35                                          | FB 20, Training Eff 10, Initial Speed 25, Fan 15, Specialty Priority 20, Speed Bonus 35 locked, Initial Friendship Gauge 45 locked |  |
| Super Creek        | FB 25, Stamina Bonus 1, Training Eff 15, Initial Stamina 35, Initial Friendship Gauge 30, Race 10, Fan 20, Specialty Priority 35                                      | FB 25, Stamina Bonus 1, Training Eff 15, Initial Friendship Gauge 30, Race 10, Fan 20, Specialty Priority 35                       |  |
| Matikanetannhauser | FB 25, Mood 30, Guts Bonus 1, Training Eff 5, Initial Guts 35, Race 5, Fan 10, Specialty Priority 65                                                                  | FB 20, Training Eff 5, Initial Guts 25, Fan 5, Specialty Priority 50, Guts Bonus 35 locked, Mood 45 locked                         |  |
| Haru Urara         | FB 20, Mood 30, Training Eff 15, Initial Guts 35, Race 5, Fan 10, Specialty Priority 50, Skill Point Bonus 1                                                          | FB 16, Training Eff 11, Initial Guts 27, Fan 6, Specialty Priority 40, Skill Point Bonus 1, Mood 45 locked                         |  |

**Correction to the first pass:** the number beside a padlock in the client popup is the card level at
which that effect unlocks, not a value. "Race Bonus 🔒 Lvl 45" means the effect arrives at level 45,
which these cards cannot reach until their limit breaks raise the cap. It is not a 45% anything.

| Card               | Hint pool (catalog)                                                                                                     | Story-event skills (catalog)                      |  |
| ------------------ | ----------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------- |  |
| Tokai Teio         | Prudent Positioning, Nimble Navigator, Go with the Flow, Thunderbolt Step, Soft Step, Shrewd Step                       | Rushing Gale!, Pace Chaser Straightaways ○        |  |
| Nishino Flower     | Hanshin Racecourse ○, Standard Distance ○, Firm Conditions ○, Updrafters, Pace Chaser Corners ○                         | Beeline Burst, Straightaway Adept, Countermeasure |  |
| Biko Pegasus       | Outer Post Proficiency ○, Wait-and-See, Gap Closer, Productive Plan, Updrafters, Meticulous Measures, Stop Right There! | Sprint Straightaways ○, Plan X                    |  |
| Super Creek        | Firm Conditions ○, Corner Recovery ○, Ramp Up, Homestretch Haste, Hesitant Pace Chasers                                 | Swinging Maestro, Deep Breaths                    |  |
| Matikanetannhauser | Lay Low, Pace Strategy, Steadfast, Deep Breaths, Fighter                                                                | Unruffled, Calm in a Crowd, Subdued Front Runners |  |
| Haru Urara         | none stored                                                                                                             | Long Shot ○, Unruffled                            |  |

This closes the per-card Skills question from the catalog rather than from the frames, and it cross-
validates the Learn screen: Soft Step at `Lv Max`, Shrewd Step and Nimble Navigator Obtained (Teio);
Pace Chaser Corners ○ at Lv4 and Firm Conditions ○ at Lv1 (Flower); Gap Closer and Productive Plan at
Lv1 and Lv3 (Pegasus); Hesitant Pace Chasers at Lv2 and Corner Recovery ○ Obtained (Creek); Steadfast
and Deep Breaths Obtained (Tannhauser); Long Shot ○ at Lv1 and Unruffled at Lv4 (Urara, from her events,
since she stores no hint pool). Skills on the Learn screen with no hint and no discount, like Ramp Up,
Calm in a Crowd and Wait-and-See, are pool members whose level never got raised. That is the
"drawn from that support card's own hint pool" rule at `02-unity-cup.md:95` working as documented.

Each card's popup also carries a **"Unity Cup Exclusive"** block: the character's own state as a team
member, since the deck's characters join the team. Those six blocks are in §1.9.

### 1.5 Inheritance, from the Sparks panels

The ancestry tree is now read directly, from the Legacy Umamusume panel (`2161e855`) and one Sparks
panel per ancestor (`fd78e996`, `31fd26c4`, `f7a6952f`, `e771563c`, `24f5b5d0`, `eea37f2a`):

```text
Rice Shower
├── Legacy 1  Vodka [Wild Top Gear]  rank B+
│     ├── Daiwa Scarlet [Peak Blue]            rank B+
│     └── Seiun Sky [Reeling in the Big One]   rank A
└── Legacy 2  Maruzensky [Formula R]  rank UG, flagged Guest
      ├── Daiwa Scarlet [Peak Blue]   rank UG
      └── Taiki Shuttle [Wild Frontier]  rank UG
```text

| Ancestor                     | Blue            | Pink             | Green unique              | White sparks                                                                                                                                                                                                                                                       |  |
| ---------------------------- | --------------- | ---------------- | ------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |  |
| Vodka                        | Power ★☆☆       | Late Surger ★★☆  | Cut and Drive! ★★☆        | Oka Sho ★☆☆, Victoria Mile ★☆☆, Shuka Sho ★★☆, Japan C. ★★☆, Extra Tank ★★☆, Soft Step ★★★                                                                                                                                                                         |  |
| Daiwa Scarlet, Legacy 1 side | Guts ★☆☆        | Mile ★☆☆         | Resplendent Red Ace ★☆☆   | Osaka Hai ★★☆, Tenno Sho (Autumn) ★☆☆, Competitive Spirit ○ ★★★, Pace Chaser Straightaways ○ ★★☆                                                                                                                                                                   |  |
| Seiun Sky                    | **Stamina ★★★** | Turf ★★☆         | Angling and Scheming ★★☆  | Kikuka Sho ★☆☆, Corner Recovery ○ ★★☆, URA Finale ★☆☆                                                                                                                                                                                                              |  |
| Maruzensky                   | Speed ★★☆       | Turf ★★☆         | Red Shift/LP1211-M ★☆☆    | Takarazuka Kinen ★★☆, Asahi Hai F.S. ★★☆, Corner Adept ○ ★★☆, Ramp Up ★★☆, Mile Straightaways ○ ★☆☆, **Ignited Spirit: Guts +** ★☆☆                                                                                                                                |  |
| Daiwa Scarlet, Legacy 2 side | Speed ★★☆       | Front Runner ★★☆ | Resplendent Red Ace ★☆☆   | Osaka Hai ★★☆, Tenno Sho (Spring) ★★☆, NHK Mile C. ★★☆, Japan C. ★★☆, Corner Adept ○ ★★☆, Unyielding Spirit ★★☆, Front Runner Savvy ○ ★☆☆                                                                                                                          |  |
| Taiki Shuttle                | **Power ★★★**   | Pace Chaser ★★☆  | Shooting for Victory! ★★☆ | NHK Mile C. ★★☆, Sprinters S. ★☆☆, JBC L. Classic ★★☆, Tokyo Daishoten ★★☆, Corner Adept ○ ★★☆, Corner Acceleration ○ ★★☆, Shifting Gears ★☆☆, Productive Plan ★★☆, Pace Chaser Straightaways ○ ★★★, Disorient ★★☆, Sympathy ★★☆, Head-On ★★☆, Glittering Star ★★☆ |  |

**These panels replace the star reads in the first version of this section, and three of them were
wrong.** Reading stars off the scrolling Sparks grid gave Shooting for Victory! ★★★ (it is ★★☆),
Resplendent Red Ace ★★☆ (★☆☆ in both branches), and a Guts ★★★ that does not exist; the Guts spark is
Daiwa Scarlet's ★☆☆. The only ★★★ blue sparks are Seiun Sky's Stamina and Taiki Shuttle's Power, and
there is **no Wit spark anywhere in the tree**, which is the clearest explanation available for Wit
sitting at 397. The consequence for the cap arithmetic in §1.2 is in that section.

Two details worth keeping: the same card, [Peak Blue] Daiwa Scarlet, appears on both branches with
different spark sets, so sparks are per-ancestor-instance rather than per-card, and Legacy 2 is flagged
**Guest** at rank UG while Legacy 1 sits at B+ and A. Rank badges here are the ancestor's own end-of-run
class, not a spark grade.

### 1.6 Unity Cup state

Team **Blue Bloom**, motto "Dreaming Big", **Team Rank S already reached**, league placement **8th**
(`eccdc650`). All four preseason rounds won: Round 1 after Junior Late Dec, Round 2 after Classic Late
Jun, Round 3 after Classic Late Dec, Round 4 after Senior Late Jun. **Finals next**, after Senior Late
Dec, five turns away.

Goals (`885d4671`): cleared are Place top 3 in Nikkei Sho (Senior Late Mar), Place 1st in Tenno Sho
(Spring) (Senior Late Apr), Place top 3 in Takarazuka Kinen (Senior Late Jun). Active: **Place 1st in
Arima Kinen**, Senior Late Dec, entry criteria met, 5 turns.

Race calendar (`9d0a0dfb`): the Senior board is empty except **Kyoto Daishoten** scheduled Early Oct
and the **Arima Kinen** goal slot at Late Dec.

**Race day, Senior Early Oct** (`85e4c75a`, `f49bd7b1`, `714f495d`). The turn offers two races and the
client marks the first one Recommended:

| Option                      | Grade | Course                                                  | Fans on a win | Her fit          |  |
| --------------------------- | ----- | ------------------------------------------------------- | ------------- | ---------------- |  |
| Kyoto Daishoten             | G2    | Kyoto Turf 2400m (Medium) Right / Outer, Fall, **Soft** | 6,700         | Turf A, Medium A |  |
| Mile Championship Nambu Hai | G1    | Morioka **Dirt** 1600m (Mile) Left                      | 6,000         | Dirt G, Mile B   |  |

The Daishoten card: Full Gate 18, held in Classic Year / Senior Year Oct, entry criteria "2,000 fans or
more" met at 209,245, and fan rewards of 6,700 / 2,680 / 1,675 / 1,005 / 670 for first through fifth,
plus item and bonus rows with the note that carats can be obtained up to 20 times per day. The
Predictions dialog shows five target rings across the stats and the trainer's verdict: "Your trainee
seems pumped up and ready for a challenge. I'd say she could be a **top contender**."

Three consequences, each of which changes a line in §2.3. The higher-grade alternative is a Dirt race
and she is Dirt G, so the G1 is the worse ride. The going is **Soft**, so a Firm Conditions skill does
not fire this turn while the sun icon makes a Sunny Days skill the one that would. And "top contender"
argues against Long Shot ○, whose condition is 4th favourite or below; the Log is still empty, so that
is the client's own estimate rather than a starting-grid reading.

The fan ladder also puts the class target in reach: 30,755 fans to Top Star, 6,700 from this G2 alone,
with the Arima Kinen and the finals still to run. The G1's payout is not in these frames, so the target
is plausible rather than assured.

### 1.7 Mood, Energy, and the last observed training turn

Mood is **GREAT**. The client's own Mood Effect panel is in the set (`bab6c92f`) and reads GREAT +20%
training / +4% running, GOOD +10/+2, NORMAL 0, BAD −10/−2, AWFUL −20/−4. That matches
`docs/UMAMUSUME_REFERENCE.md` §1.1.6 line for line, so it re-confirms the race-side figure this repo
settled once already against the wikis.

Energy is low: the bar sits near a third, and the game is printing the warning "Your trainee's energy
is getting low, so she's more susceptible to injury. Rest is important too." with her own line "Phew...
I guess I'm just... a little tired..." (`297b931b`). The client never prints an Energy number, so the bar
is the only readout, and the usable signals are its fill colour, which changes at the top of the range,
and that warning text. Between the two a Trainer can identify the low band but cannot measure it. The
tile's failure percentage is the one Energy-adjacent number the client does state, and whether it can be
inverted to an Energy value is with the research pass.

The training screen (`b1332cb3`): Speed facility **Lv4** with the banner "Floor Cleaning", three card
avatars on the tile, preview **+18 Speed / +8 Power / +6 Skill Points**, and **Failure 39%**. Facility
levels: Speed 4, Stamina 4, Power 4, **Guts 5**, Wit 4.

---

### 1.8 Team Info panel

The control beside the Team Rank display, opened (`3cc1c271`, `556e1d6c`):

| Field                 | Value                                                       |  |
| --------------------- | ----------------------------------------------------------- |  |
| Team                  | Blue Bloom, motto "Dreaming Big", league **8th**            |  |
| Team Rank             | S (five stars), **Team Ranking Bonus: All Attributes + 30** |  |
| Team stat grades      | Speed A, Stamina A, Power A, **Guts S**, Wit A              |  |
| Unity Trainings       | **55**                                                      |  |
| Spirit Bursts         | **6**                                                       |  |
| Extreme Spirit Bursts | **5**                                                       |  |
| Members               | **20 / 20**                                                 |  |

Two of these are decision-bearing. **11 combined bursts** (6 + 5) sits in the 10–12 band, so the
November event pays the **gold** scenario skill at Lv1, and 13 would pay it at Lv3. And the team's
highest grade is **Guts (S)**, which is what selects the stat variant, so the incoming gold skill is
**Burning Spirit GUTS**. The five Extreme Bursts also explain the four Ignited Spirit hints on the Learn
screen, which is the mechanic at `02-unity-cup.md:109` rather than anything from the deck's hint pools.

The Team tab lays the roster out as five distance columns, Sprint, Mile, Medium, Long and Dirt, with an
**ACE** slot plus two more in each, matching the one-to-three-per-distance rule at
`02-unity-cup.md:170`. Reading the ACE row off the aptitude tags: Sprint ACE Seeking the Pearl (Sprint
A), Mile ACE Nishino Flower (Mile A), Medium ACE Tokai Teio (Medium A), Long ACE the trainee herself
(Long A), Dirt ACE Haru Urara (Dirt A).

### 1.9 The roster

Every member carries a stat line in the client. The six deck characters appear as teammates in their own
card popups; the rest arrive as **Guest** R cards tagged `[Tracen Academy]`. Format is
Speed / Stamina / Power / Guts / Wit, then the caps the client shows.

| Deck character     | Grade | Stats                            | Caps                  | Aptitudes of note                        |  |
| ------------------ | ----- | -------------------------------- | --------------------- | ---------------------------------------- |  |
| Haru Urara         | **A** | 733 / 572 / 728 / **1068** / 621 | 900/860/900/1140/850  | Dirt A, Sprint A, Late A; Turf G         |  |
| Tokai Teio         | B     | 885 / 571 / 702 / 754 / 597      | 1080/860/960/880/850  | Turf A, Medium A, Pace A                 |  |
| Nishino Flower     | B     | 806 / 591 / 680 / 625 / 610      | 1080/860/960/880/850  | Turf A, Sprint A, Mile A, Pace A, Late A |  |
| Super Creek        | B     | 563 / 888 / 527 / 764 / 633      | 860/1100/860/1000/850 | Turf A, Medium A, Long A, Pace A         |  |
| Biko Pegasus       | C     | 776 / 534 / 660 / 593 / 534      | 1080/860/960/880/850  | Turf A, Sprint A, Late A                 |  |
| Matikanetannhauser | C     | 431 / 418 / 423 / 509 / 377      | 710/700/710/750/700   | Turf A, Medium A, Long A, Pace A, Late A |  |

| Guest teammate     | Grade | Stats                       | Caps                | Aptitudes of note                                |  |
| ------------------ | ----- | --------------------------- | ------------------- | ------------------------------------------------ |  |
| Fine Motion        | B     | 643 / 599 / 620 / 664 / 549 | 770/750/750/750/750 | Turf A, Mile A, Medium A, Pace A                 |  |
| King Halo          | B     | 603 / 556 / 633 / 725 / 526 | 800/750/770/750/700 | Turf A, Sprint A, Late A                         |  |
| Seeking the Pearl  | C     | 655 / 560 / 600 / 581 / 533 | 710/700/710/750/700 | Turf A, Sprint A, Mile A, Late A                 |  |
| Shinko Windy       | C     | 566 / 544 / 566 / 556 / 528 | 750/700/720/700/700 | Dirt A, Mile A, Pace A                           |  |
| Inari One          | C     | 576 / 597 / 616 / 556 / 569 | 700/720/750/700/700 | Turf A, Dirt A, Medium A, Long A, End A          |  |
| Nakayama Festa     | C     | 533 / 475 / 504 / 666 / 502 | 700/750/700/720/700 | Turf A, Medium A, Late A                         |  |
| Agnes Digital      | C     | 504 / 493 / 552 / 650 / 494 | 700/720/750/700/700 | Turf A, Dirt A, Mile A, Medium A, Pace A, Late A |  |
| Eishin Flash       | C     | 540 / 501 / 497 / 568 / 542 | 750/700/720/700/700 | Turf A, Medium A, Long A, Late A                 |  |
| Matikanefukukitaru | C     | 501 / 483 / 513 / 532 / 543 | 830/780/780/790/900 | Turf A, Medium A, Long A, Late A                 |  |
| Taiki Shuttle      | C     | 411 / 411 / 467 / 398 / 375 | 750/700/720/700/700 | Turf A, Sprint A, Mile A, Pace A                 |  |
| Gold Ship          | C     | 475 / 426 / 455 / 403 / 389 | 700/750/700/720/700 | Turf A, Medium A, Long A, End A                  |  |

That is 17 named members plus the trainee. The Roster grid's bottom row is clipped in the capture, so
two members are unaccounted for against the 20/20 count.

The guests carry their own skill lists, which is where the R-card hint pool comes from
(`02-unity-cup.md:95`): Seeking the Pearl (Firm Conditions ○, Pace Strategy, Sprinting Gear, Watchful
Eye, Sprint Straightaways ○, Unyielding Spirit); Taiki Shuttle (Preferred Position, Prepared to Pass,
Productive Plan, Updrafters, Mile Straightaways ○, Shifting Gears); Agnes Digital (Lay Low, Calm in a
Crowd, Mile Straightaways ○, Opening Gambit, Medium Straightaways ○, Late Surger Straightaways ○);
Shinko Windy (Wet Conditions ○, Competitive Spirit ○, Prepared to Pass, Hesitant Front Runners);
Matikanefukukitaru (Hakodate Racecourse ○, Trick (Rear), A Small Breather, Triple 7s, Calm in a Crowd,
Late Surger Corners ○, Lucky Seven); Inari One (Spring Runner ○, Target in Sight ○, Sharp Gaze, Forward
March!, Winter Runner ○, Ramp Up, Tail Held High, Familiar Ground); Eishin Flash (Standard Distance ○, <!-- lore-ignore-line class=3 cite=C-4 -->
Late Surger Straightaways ○, Fighter, Straightaway Acceleration, Late Surger Corners ○); Gold Ship
(Standing By, Inside Scoop, Smoke Screen, After-School Stroll, Intense Gaze, Highlander, Straightaway
Spurt, Pressure, I Can See Right Through You, Strategist, End Closer Savvy ○, Uma Stan); Nakayama Festa
(Wet Conditions ○, Slick Surge, Steadfast, Sharp Gaze, All I've Got, Long Shot ○, Outer Swell, Late
Surger Straightaways ○, Lucky Seven); Fine Motion (Right-Handed ○, Outer Post Proficiency ○, Nimble
Navigator, Straightaway Acceleration, Fall Runner ○); King Halo (Firm Conditions ○, Outer Post
Proficiency ○, Wait-and-See, Cloudy Days ○, Corner Recovery ○, Gap Closer).

Note the caps: teammates top out between 700 and 1140, not at the trainee's 1300/1800, so a team Guts
grade of S is being carried by Haru Urara's 1068 rather than spread evenly.

---

## 2. The skill pool, and what 173 SP buys

Fourteen Learn-screen scrolls give the whole purchasable pool with its client prices. This is the
section the first pass could not write.

### 2.1 The hint discount ladder, read off the client

The Learn screen prints the discount itself, `Hint Lvl N` above `NN% OFF`:

| Caption        | Discount | Observed on                                                                                                                                                                |  |
| -------------- | -------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |  |
| `Hint Lvl 1`   | 10%      | Rushing Gale!, Outer Swell, Ignited Spirit SPD/PWR, Plan X, Kyoto Racecourse ○, Resplendent Red Ace, Firm Conditions ○, Sunny Days ○, Inner Post Proficiency ○, Gap Closer |  |
| `Hint Lvl 2`   | 20%      | Hesitant Pace Chasers, Cut and Drive!, Countermeasure, Hakodate Racecourse ○                                                                                               |  |
| `Hint Lvl 3`   | 30%      | It's On!, Ignited Spirit STA, Front Runner Corners ○, Productive Plan                                                                                                      |  |
| `Hint Lvl 4`   | 35%      | Unruffled, Pace Chaser Straightaways ◎, Pace Chaser Corners ○, Ignited Spirit GUTS                                                                                         |  |
| `Hint Lvl Max` | 40%      | Soft Step                                                                                                                                                                  |  |

Displayed cost is base × (1 − discount), floored to the integer. Bases 130, 160, 180 and 200 all
reconcile: 130 → 104/91/84, 160 → 144/128/112/96, 180 → 162, 200 → 180/140/130. The one fractional case
is Pace Chaser Corners ○, 130 × 0.65 = 84.5 shown as 84.

This is the in-client read the repo was waiting on. It is recorded in
`docs/research-scratch/SKILLS-MECHANICS.md` §2.4 and it closes the open gap in
`docs/UMAMUSUME_REFERENCE.md` §1.1.4. It also explains the levels this deck produces: Burst hints start
at card hint bonus + 2, +1 more for a scenario-linked card (`02-unity-cup.md:95`), and Nishino Flower
carries Hint Levels Lv2, which lands exactly on the Lv4 captions seen on the Pace Chaser line.

### 2.2 Purchasable at or under 173 SP

| Skill                       | Cost | Hint   | Gate or note                                                   |  |
| --------------------------- | ---- | ------ | -------------------------------------------------------------- |  |
| Pace Chaser Straightaways ◎ | 91   | Lv4    | Style match. The tier above the ○ she owns                     |  |
| Pace Chaser Corners ○       | 84   | Lv4    | Style match                                                    |  |
| Straight Descent            | 120  | none   | (Pace Chaser)                                                  |  |
| Subdued Pace Chasers        | 130  | none   |                                                                |  |
| Subdued Front Runners       | 117  | Lv1    |                                                                |  |
| Hesitant Pace Chasers       | 104  | Lv2    |                                                                |  |
| Ignited Spirit GUTS         | 130  | Lv4    | Scenario skill, late-race vigor scaled to team Guts total      |  |
| Ignited Spirit STA          | 140  | Lv3    | Scenario skill, mid-race recovery scaled to team Stamina total |  |
| Cut and Drive!              | 160  | Lv2    | Front-half, last 200m                                          |  |
| Outer Swell                 | 162  | Lv1    | (Late Surger). She is Late B                                   |  |
| Highlander                  | 160  | none   |                                                                |  |
| Ramp Up                     | 170  | none   |                                                                |  |
| Straightaway Acceleration   | 170  | none   |                                                                |  |
| Calm in a Crowd             | 170  | none   |                                                                |  |
| Soft Step                   | 96   | Lv Max |                                                                |  |
| Countermeasure              | 128  | Lv2    | (Sprint). She is Sprint E                                      |  |
| Gap Closer                  | 144  | Lv1    | (Sprint)                                                       |  |
| Productive Plan             | 112  | Lv3    | (Mile)                                                         |  |
| Long Shot ○                 | 81   | Lv1    | 4th favourite or below                                         |  |
| Kyoto Racecourse ○          | 81   | Lv1    | This turn's Daishoten                                          |  |
| Hakodate Racecourse ○       | 56   | Lv2    |                                                                |  |
| Firm Conditions ○           | 81   | Lv1    |                                                                |  |
| Sunny Days ○                | 81   | Lv1    |                                                                |  |
| Inner Post Proficiency ○    | 81   | Lv1    |                                                                |  |
| Front Runner Corners ○      | 91   | Lv3    | Wrong style                                                    |  |

The Unity Cup line on the Learn screen is **Ignited Spirit SPD / STA / PWR / GUTS** at 180/140/180/130,
all four on a base of 200 and separated only by hint level. The series runs to five per the client
strings (速 / スタ / パワ / 根性 / 賢さ), so an Ignited Spirit WIT exists and simply is not hinted in
this deck; the frames not showing it is not a gap in the series
([game8.jp, ⚠️ STALE: 2025-11-20](https://game8.jp/umamusume/400803), names cross-checked against
[game8.co, 2026-09-23](https://game8.co/games/Umamusume-Pretty-Derby/archives/563893)). Four of the
five are hinted here, and the Team Info panel says why: **6 Spirit Bursts plus 5 Extreme Bursts**, the
Extremes being what grants the matching facility's Ignited Spirit hint (`02-unity-cup.md:109`). No
**Burning Spirit** entry appears because the gold half of the line (燃焼) is paid by the November event
rather than by a Burst, and that event has not fired yet. At 11 combined the event pays gold Lv1, and
two more bursts take it to Lv3. See §2.4.

**Out of reach at 173:** Ignited Spirit SPD and PWR 180, Resplendent Red Ace 180, Plan X 272, Unruffled
280, It's On! 289, Rushing Gale! 323.

### 2.3 Recommended spend

Advice, not a prediction.

1. **Pace Chaser Straightaways ◎, 91.** Cheapest style-matched velocity on the board, and the tier
   above a skill she already holds.
2. **One 81 with the remaining 82, chosen for the race you actually run.** Kyoto Racecourse ○ fires at
   the Daishoten this turn. The going there is **Soft**, so Firm Conditions ○ would not fire, while the
   sun on the race card makes Sunny Days ○ the condition skill that would. Long Shot ○ is now the
   weakest of the four: the client's own Predictions verdict for this race is "top contender", and the
   skill needs 4th favourite or below.
3. **Scenario skills: hold off until the November question is answered.** The Team Info count says the
   event will pay **Burning Spirit GUTS** (gold) at Lv1, and the team's Guts grade is what selects it.
   Buying the white **Ignited Spirit GUTS** now, at 130, is either the prerequisite for that gold or a
   skill the gold supersedes, and neither the corpus nor the frames state which; the catalog's skill
   table carries no prerequisite column either. That single unknown decides whether 130 SP is an
   investment or a write-off, so it is the one thing worth checking in the client before spending. If
   the gold is coming regardless, **Ignited Spirit STA at 140** is the better buy of the two on this
   build: 577 Stamina over 2500 m is her actual weakness, and the Guts side is already carried.
4. **Do not buy into a gate she fails.** Countermeasure and Gap Closer are Sprint, Productive Plan is
   Mile, Outer Swell is Late Surger, Front Runner Corners is the wrong style.
5. **Save It's On! and Unruffled for a longer run.** Both are golds this deck can hint, and neither is
   reachable now.

### 2.4 The burst economy, and the one deadline inside the five turns

From the research pass, with what the frames add:

- **Charge.** A teammate's gauge moves one quarter per Special Training she joins, so roughly four
  sessions fill her
  ([umamusu.wiki, 2026-09-17](https://umamusu.wiki/Game:Unity_Cup);
  [famitsu, ⚠️ STALE: 2021-09-02](https://www.famitsu.com/news/202109/02232227.html) gives the same
  "about four"). No source publishes a per-turn curve beyond that.
- **One normal Burst and one Extreme per teammate per career.** The Extreme is a low-probability
  follow-on, it can be skipped and come back, and it locks after one activation
  ([kamigame, ⚠️ STALE: 2024-04-10](https://kamigame.jp/umamusume/page/172795448925364187.html);
  [game8.co 545572, 2026-07-07](https://game8.co/games/Umamusume-Pretty-Derby/archives/545572)). Both
  kinds count toward the same career total (`02-unity-cup.md:201`, confirmed by
  [gametora, 2026-07-20](https://gametora.com/umamusume/unity-cup)).
- **The count is cashed at one scripted event, not continuously.** "Team Zenith Declares War" pays the
  scenario hint by total bursts: 4–6 white Lv1, 7–9 white Lv3, 10–12 gold Lv1, 13+ gold Lv3, with the
  stat variant chosen by the team's highest overall rank and the value scaling on the team's total in
  that stat. **This run reads 11 combined (6 + 5) on the Team Info panel, inside the 10–12 band, so the
  gold is already banked at Lv1 and the only question is whether two more bursts land in time for Lv3.**
  The team's highest grade is Guts (S), so the variant is GUTS.
- **Deadline.** That event is in Senior November, which is inside the five turns. Bursts fired after it
  do not raise the tier, so charging and firing belongs early in the window, not before the finals.
- **Readout.** The running count is available on demand: the control beside the Team Rank display opens
  combined drill counts and burst activations
  ([game8.co 545572, 2026-07-07](https://game8.co/games/Umamusume-Pretty-Derby/archives/545572),
  matching `02-unity-cup.md:212`). That panel is captured, in §1.8.

**Three discrepancies against `docs/scenarios/02-unity-cup.md`, flagged rather than fixed:**

1. **Date.** That file's `:210` says the payout event is Senior **late** November and records that it
   had deliberately moved the date off "early November". The publishers read the other way: game8.jp
   prints シニア11月前半の練習後, "after practice in the **first half** of November"
   ([⚠️ STALE: 2025-11-20](https://game8.jp/umamusume/400803)), and game8.co says "Senior Year Early
   November" ([2025-11-07](https://game8.co/games/Umamusume-Pretty-Derby/archives/563908)). gamewith
   words it as the start of November's second half
   ([⚠️ STALE: 2023-01-20](https://gamewith.jp/uma-musume/article/show/287164)), which is the only
   support the late reading has. Planning consequence either way is the same, so this is a doc-currency
   question, not a strategy one.
2. **Naming.** `:210` withdraws the "Ignited Spirit X" / "Burning Spirit X" tier names in favour of a
   white/gold framing. The client prints those names: four Learn entries read **Ignited Spirit SPD / STA
   / PWR / GUTS** (`c1ee4d11`, `c8b2dcad`), and the publishers carry the gold counterpart as Burning
   Spirit ([game8.co, 2026-09-23](https://game8.co/games/Umamusume-Pretty-Derby/archives/563897)). The
   white/gold framing is a fine description; the claim that the names were superseded is contradicted by
   the screen.
3. **Payload.** The `:205-208` table's "+10 stat / +10 SP … +40 / +40" is gametora's
   ([2026-07-20](https://gametora.com/umamusume/unity-cup)) and is single-source. game8.co
   ([2026-07-07](https://game8.co/games/Umamusume-Pretty-Derby/archives/545572)) and game8.jp
   ([2025-11-20](https://game8.jp/umamusume/400803)) print **15 / 15 / 20 / 20 SP** with no stat
   component at all. Two publishers against one, on a figure this file treats as settled.

---

## 3. What this run looks like it was doing

The build is coherent and unusual: Guts as the primary stat (736, highest on the board) with +20%
growth, two Guts cards, Super Creek carrying Stamina, and three Speed cards doing base work. Wit is
deliberately abandoned at 397, and the deck has no Wit card to fix it. Medium and Long at A with Pace
A, plus Swinging Maestro already owned, so Arima Kinen at 2500 m is a race she is shaped for. All four
team rounds won and Team Rank S banked means both headline Unity Cup objectives are already secured.

---

## 4. What is closed off, and one thing the corpus cannot settle

The Elite Team path is **past its window**, not necessarily failed. The condition is checked during the
**4th Team Race** (`02-unity-cup.md:159`), which this run already won, after Senior Late Jun. Whether
an Elite team actually appeared there is not in any frame.

The first pass of this file called the path unavailable because placement is 8th and the corpus states
the condition as "league rank is ≥ 10" (`02-unity-cup.md:161`). That reading is not safe: the doc
writes the rank as a number with a floor sign, and a league position of 8th satisfies "top 10" while
failing "10 or higher". The two readings invert the verdict. Flagged for the owner rather than fixed
here, since it is a doc defect in `docs/scenarios/02-unity-cup.md`, not a finding about this run.

Against the three Elite conditions (`02-unity-cup.md:161-163`): Team Rank S clears the ≥ A bar, and at
least one Extreme Burst has almost certainly fired, since four distinct Ignited Spirit hints are on the
Learn screen and the Extreme Burst is what pays them. That leaves the league-rank reading as the only
thing standing between this run and the path, and the window closed anyway at Round 4.

What follows either way: the finals opponent is standard Team Zenith unless the strengthened variant was
unlocked, and beating Zenith while at Rank S is what grants the team name's skill, **Cooldown** for Blue
Bloom (`02-unity-cup.md:183`, `:156`). Picking the name is not enough; the win is.

---

## 5. The plan for the five remaining turns

1. **Energy first, this turn.** A 39% failure roll on a strong tile is a bad trade with five turns
   left. In order: an Extreme Burst tile (0% failure, scoped to the facility holding the Burst,
   `02-unity-cup.md:113`), a Wit turn (no Energy cost, and a Burst there returns +5 Energy), or Rest
   for Energy. Corrected 2026-10-03: this step was written as "Rest for +30 Energy" on
   `docs/UMAMUSUME_REFERENCE.md:162`, and the repository's own §2.1 in
   `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` measures the modal rest at +50 with +30 as
   the under-slept failure tier. The order of the three options does not depend on which tier lands;
   how many training turns the rest buys back does, so the figure is left open here rather than picked.
2. **Run the Kyoto Daishoten rather than training into the risk.** It is scheduled Early Oct, it costs
   no Energy, and Race Bonus effects on the deck pay stats and fans from finishing: 6,700 fans on a win,
   which is 22% of the gap to Top Star. The G1 on the same card is Morioka Dirt 1600m and she is Dirt G,
   so the higher grade is the worse ride.
3. **Then committed training turns on the best tiles.** Guts is Lv5 and holds both Guts cards, so it is
   the highest-value facility, and the multi-flame curve rewards crowding: the trainee gains nothing
   below 2 flames, and 5 flames is 10 primary / 5 secondary / 7 SP
   (`02-unity-cup.md:77`, `:83-90`).
4. **Two more bursts before the November cut-off, to turn the gold Lv1 into a Lv3.** The count is
   already 11, so Burning Spirit GUTS is banked at Lv1; 13 lifts the level. Early Nov is the third of the
   five turns and Late Nov the fifth, so the window is two or three turns wide depending on how the date
   discrepancy in §2.4 resolves. On the estimate that a gauge needs four sessions, 11 bursts accounts for
   44 of the 55 Unity Trainings logged, leaving about eleven sessions of partial progress spread across
   the members, so two fills inside the window is arithmetic that works if the tiles are chosen for it.
   A Burst fired after the event still raises stats but no longer buys a tier.
5. **Enter Late Dec at full Energy and GREAT mood.** Late Dec holds both Arima Kinen and the Unity Cup
   finals. Mood is already GREAT, so Recreation is only worth it if an event drops it.
6. **Spend the 173 SP per §2.3.** The first pass could not say what was affordable; it now is.

---

## 6. What is still missing

Three sources closed items during the session: the 17:07 set, the seeded catalog, and the 17:53 race-day
and Sparks panels. The Learn screen, the trainee's Skills tab and the Perks aggregate were already in
hand from 16:27.

**Closed this round:**

- **Burst numbers.** Team Info reads 55 Unity Trainings, 6 Spirit Bursts, 5 Extreme Spirit Bursts
  (§1.8). That is the input §2.4 and §5 needed.
- **The per-card skill lists.** Read from the catalog's `hint_skills` and `event_skills` (§1.4) rather
  than from a tab that was never opened, and they reconcile with every hint level on the Learn screen.
- **Team roster and Team Race composition.** §1.9, minus two members clipped out of the capture.
- **Team Power, as far as the client shows it.** Grades A/A/A/S/A plus the +30 ranking bonus (§1.8).
  No single power total appears on that screen.
- **Deck effect sets.** The catalog carries each card's full effect list at maximum values (§1.4),
  including the rows the client has locked behind card level.

- **Ancestry tree.** Six Sparks panels, one per ancestor, plus the Legacy panel (§1.5). The tree is
  Vodka under Daiwa Scarlet and Seiun Sky, and a Guest Maruzensky under Daiwa Scarlet and Taiki Shuttle.
- **This turn's race card, and a proxy for the favourite question.** The Daishoten's full spec is in
  §1.6, and the client's own "top contender" verdict stands in for the starting grid.

**Still open.** Items 1, 2, 3 and the league-rank half of 4 went to an online research pass on
2026-10-03, since the Trainer has no in-client answer for them either.

1. **The white-to-gold question.** Whether owning Ignited Spirit GUTS is a prerequisite for the gold
   Burning Spirit GUTS the November event will hint, or is superseded by it. It decides a 130 SP buy.
   Nothing in the repo, the catalog or the frames answers it, and the client has no screen that states a
   skill prerequisite.
2. **What the Unity Cup finals actually are as a race.** Whether the finals match has a venue, distance
   and ground at all, what sets the opposing lineup, and what the win grants beyond the team-name skill.
   The pool offers Kyoto ○ and Hakodate ○ and **no Nakayama**, so nothing maps the Arima Kinen to a
   venue skill either.
3. **Cap composition, remainder.** Now a measured shortfall rather than an unexamined number: the six
   blue sparks in §1.5 account for the Guts +8 and nothing else (§1.2). What raises the other three
   extras, and whether any Global card carries a non-zero Max-Stat value, is unresolved.
4. **League placement and NPC bonds.** How the placement number is computed, and whether 8th satisfies
   or fails the Elite Team's "league rank is ≥ 10" (§4). Bond state has no screen the Trainer can point
   to, so it is likely not obtainable from frames at all.
5. **Exact Energy value.** The client prints no number; the readable signals are the bar colour and the
   warning text (§1.7). Whether the tile's failure percentage can be inverted to a value is with the
   research pass.
6. **Race history.** The Log reads "There is no log" in every frame and cannot be supplied. Closed as
   unavailable; the Predictions verdict is the substitute for the favourite question, and the Long Shot
   ○ call in §2.3 is made on it.
7. **Two roster entries.** The Trainer confirms §1.9 lists every Guest teammate, which makes the named
   total 18 against the panel's 20/20 counter. Either the clipped row repeats characters already listed
   or the count includes someone the grid does not show. Left as an arithmetic discrepancy, not a
   screenshot gap.

---

## 7. Corpus findings, and the edits they caused

Eight findings came out of the 67 frames, the research pass and the catalog cross-check: two are
recorded in the corpus, one is corroboration, two are flagged rather than applied, and three are things
the later sources revealed that the frames alone had wrong or left unstated.

1. **The per-level hint discount** is now client-read: 10/20/30/35/40% at Lv1/Lv2/Lv3/Lv4/`Lv Max`.
   Recorded in `docs/research-scratch/SKILLS-MECHANICS.md` §2.4 with its caption table and arithmetic,
   and it closes the `❌ UNVERIFIED` line in `docs/UMAMUSUME_REFERENCE.md` §1.1.4 plus the matching
   entry in §8.4's remaining-gaps list. §2.2's standing position that a percentage may not be rendered
   in application code, and conflict row 16, were left alone; those are the owner's to revisit.
2. **The export's `stat_bonus` row is the Growth Rate display.** Her Details screen carries Potential
   Level as a single number and, separately, a five-cell Growth Rate row reading +0/+10/+0/+20/+0,
   which is `stat_bonus: [0, 10, 0, 20, 0]` in GameTora's column order, position for position. The
   three July captures of this same trainee agree, and Special Week's published pair agrees across two
   sources. The costume-varying evidence (`[0,10,0,20,0]`, `[0,15,15,0,0]`, `[0,20,0,10,0]` across her
   three) is what retires the old Potential Level guess, which the first pass of this file had left
   standing as "very likely". Recorded in `docs/UMAMUSUME_REFERENCE.md` §1.3.5 and the §3.1 reading
   note. Scope stated there: two trainees, one of them read in-client by this document.
3. **The Mood Effect panel re-confirms §1.1.6** at ±20% training and ±4% running. No edit; the reference
   already carries these figures and the client agrees with them.
4. **`docs/scenarios/02-unity-cup.md` needs three corrections and two confirmations** (§2.4). The
   confirmations: the combined normal-plus-Extreme count (`:201`) and the Team Info readout beside the
   Team Rank (`:212`) both hold up against publishers. The corrections: the payout event is dated late
   November at `:210` where two publishers print the first half of the month; the payload row at
   `:205-208` rests on one publisher against two that report 15/15/20/20 SP and no stat component; and
   `:210`'s withdrawal of the "Ignited Spirit" name is contradicted by the client, which spells it on
   four Learn entries. The date and the payload are publisher conflicts this repo has already ruled on,
   so they are yours to reopen. The name is not a judgment call, and that line is wrong as written.

5. **The client prints a Global team-rank bonus, and `02-unity-cup.md` does not carry it.** The Team Info
   panel's tooltip reads "Team Ranking Bonus / All Attributes + 30" beside the S emblem (`3cc1c271`).
   That file's `:152` explicitly sets the "+50 all stats at Rank 5+" figure aside as `[JP]`-side and says
   to treat the letter ladder as the live Global one, but it never states what the Global ladder pays.
   This is the first Global number for it, and it is flagged rather than written in because the tooltip's
   binding to the rank, rather than to the 8th-place league standing it sits under, is inferred from
   position on screen.
6. **The catalog beat the frames on the deck, and corrected this file twice.** The epithet is
   `[Just Keep Going]` with no trailing exclamation mark, in the catalog and in the client header; the
   first pass printed one. And the number beside a padlock in a card popup is the card level the effect
   unlocks at, not a percentage; the first pass listed those as values. Both are in §1.4 now. The
   scenario-link claim this file previously supported with a corpus citation is available better: the
   application derives it, and `SupportCard::isScenarioLink('unity_cup')` returns yes for Haru Urara and
   no for the other five, against `config('scenarios.php')`.

7. **Star ranks have to be read off the per-ancestor Sparks panel, not the scrolling grid.** The grid
   gave Shooting for Victory! ★★★, Resplendent Red Ace ★★☆ and a Guts ★★★ that does not exist in the tree;
   the panels give ★★☆, ★☆☆ and nothing. That is not cosmetic: the cap arithmetic in §1.2 was built on
   the grid values, and against the real six blue sparks three of the four cap extras stop being
   reproducible. Worth carrying into the corpus's inheritance section as a method note.
8. **Two facts the card popups cannot show.** The sixth deck slot is a **friend card**, visible only as a
   "Friends" pill on the Career Profile rail, which is why the deck's only 50/50 card is not the
   trainer's own. And the going on this turn's race is **Soft**, which invalidates the Firm Conditions
   recommendation the first version of §2.3 made while conditions were still unknown.

Verification run on those edits: retired literals grep to zero, each new marker greps to one, the
concurrent thread's two uncommitted hunks in `docs/UMAMUSUME_REFERENCE.md` are still present in the
diff, added lines are clean against the banned-vocabulary pattern, and `composer lore` reports its
pre-existing 187/76 baseline. Nothing is committed.

---

## 8. Research pass results, 2026-10-03

The four open questions went to an online pass over Global and JP publishers. This section records what
came back and names each place above it supersedes. Publisher dates are given; the repo marks anything
older than 90 days, and this scenario was reworked in July 2026, so pre-rework pages are flagged rather
than trusted.

### 8.1 The white-to-gold question is answered: the white is the prerequisite

`§6 item 1` and `§2.3 step 3` are closed. game8.co's Burning Spirit page lists the Ignited Spirit
variant as its prerequisite and calls it the superior replacement
([2026-09-23](https://game8.co/games/Umamusume-Pretty-Derby/archives/563898)), and the JP side mirrors it
both ways: game8.jp's アオハル点火・速 carries 「上位スキル：アオハル燃焼・速」
([2026-10-02](https://game8.jp/umamusume/511396)) and GameWith's アオハル燃焼・速 lists the 点火 skill as
its 下位スキル ([2026-09-28](https://gamewith.jp/uma-musume/article/show/292878)). So spending 130 SP on
**Ignited Spirit GUTS is an investment, not a write-off.** That reverses §2.3's hold recommendation.

Still not established, and it matters: whether learning the gold suppresses or replaces the white in a
race. No page on either side states it. The tiering plus a shared mid-race trigger and a shared scaling
input is the shape of a strict upgrade, but that is inference and is recorded as such.

The base price closes a loop with §2.1: publishers give **200 SP** for both tiers, and this run's client
shows Ignited Spirit GUTS at 130, which is 200 × 0.65, the Lv4 discount. An independent source confirms
the base the discount arithmetic was derived from.

### 8.2 Whether Extreme Bursts count toward the gold is a live disagreement, and it decides this run

`§2.4` and `§1.8` assert 11 combined bursts puts the run in the gold band. That holds only if Extremes
count, and the sources split:

- gametora: tiers are based on "the number of Spirit Bursts **and Extreme spirit bursts** done during the
  career" ([2026-07-20](https://gametora.com/umamusume/unity-cup)). Combined 11 → **gold Lv1**.
- umamusu.wiki: the Burning Spirit criteria read "At least 10 Spirit Bursts by Senior Year Late November"
  and never say whether Extremes increment it
  ([2026-09-17](https://umamusu.wiki/Game:Unity_Cup)).
- game8.jp: 「アオハル爆発を行った回数」, no mention of 極 in the counting rule
  ([⚠️ STALE: 2025-11-20](https://game8.jp/umamusume/400803)).

If only normal bursts count, this run is at 6 and the November event pays **white Lv3 plus 15 SP**, not
gold. The plan in §5 step 4 changes shape under that reading: two more bursts would be reaching the 7–9
white band's ceiling rather than the 13 gold Lv3 step. Do not treat either outcome as settled; the count
is on the Team Info panel and the client is the tiebreaker.

The same page adds a third acquisition route worth noting: umamusu.wiki lists the Ignited variant as
obtainable from an Extreme Burst during matching training **and by inheritance**, which matches the
★☆☆ "Ignited Spirit: Guts +" spark from Maruzensky in §1.5.

### 8.3 The finals are five races, not one, and that is why no single race card exists

`§6 item 2` is closed. umamusu.wiki's row reads "The Unity Cup Finals | Senior Year Late December | 5 |
Team Zenith" and the set "includes all distances on turf and a mile race on dirt"
([2026-09-17](https://umamusu.wiki/Game:Unity_Cup)); the JP original names the five as 短距離／芝,
マイル／芝, 中距離／芝, 長距離／芝, マイル／ダート, with venues fixed by the opponent side and the
finals opponent locked to Riko Kashimoto's team
([⚠️ STALE: 2024-08-30](https://umamusume.wikiru.jp/)), which the official JP page confirms as
「VSチーム＜ファースト＞」「全部で5戦！」 ([undated, official](https://umamusume.jp/contents/game/scenario/aoharu/)).

This is what the client's Team tab five-column layout was for: the Sprint, Mile, Medium, Long and Dirt
columns in §1.8 **are** the finals programme, so the ACE per distance is the lineup that fights it. The
question "what are the finals' distance, venue and ground" was the wrong question, and §2.3's condition
skill should be chosen for the Arima Kinen and for the five-category meet, not for one race.

Rewards beyond the team-name skill are corroborated: a finals win grants the team-name gold, improves
scenario-spark odds, and unlocks the "+" scenario spark variants.

### 8.4 The league-rank ambiguity resolves in favour of this run: 8th qualifies

`§4` and `§7 item 4` are settled. The JP condition is 「チームランキングが10位以上」
([⚠️ STALE: 2024-08-30](https://umamusume.wikiru.jp/);
[⚠️ STALE: 2023-01-20](https://gamewith.jp/uma-musume/article/show/287164)), and 10位以上 means 10th or
better. The Global renderings agree once translated: umamusu.wiki says "demands **placing tenth or
above**" ([2026-09-17](https://umamusu.wiki/Game:Unity_Cup)) and gametora "if your league rank is at
least 10" ([2026-07-20](https://gametora.com/umamusume/unity-cup)). So `02-unity-cup.md:161`'s
"league rank is ≥ 10" is a ranking-quality condition, **8th satisfies it**, and the run's own Elite Team
gate reads: rank S clears the ≥ A bar, at least one Extreme fired, league 8th qualifies. All three held
going into Round 4. Whether an Elite team actually appeared there is still not in any frame.

Also from the JP pages: the league starts at 30th and moves by beating higher-ranked teams, which is
what "8th" is measured against.

### 8.5 The cap shortfall gains a named candidate, and one mechanism is ruled out

`§1.2` and `§6 item 3` narrow. Two changes:

- **Bursts are ruled out for the trainee's own caps.** The official JP page says 「チームメンバーの能力が
  上限を超えて大幅アップ！」 ([undated, official](https://umamusume.jp/contents/game/scenario/aoharu/))
  and both Global sources agree the target is the teammate
  ([umamusu.wiki 2026-09-17](https://umamusu.wiki/Game:Unity_Cup),
  [gametora 2026-07-20](https://gametora.com/umamusume/unity-cup)). So the 5 Extremes this run fired do
  not explain her 1325/1322/1368/1308.
- **Green (unique-skill) factors are the candidate.** GameWith states cap-UP lives in blue and green
  factors, and that a green factor raises the cap of the stat tied to that character
  ([⚠️ STALE: 2023-06-28](https://gamewith.jp/uma-musume/article/show/360428)); game8.jp puts its value at
  the character's growth rate ([⚠️ STALE: 2025-11-21](https://game8.jp/umamusume/475668)). This run's tree
  carries **six** green factors: Cut and Drive!, Angling and Scheming, Resplendent Red Ace ×2, Red
  Shift/LP1211-M and Shooting for Victory!. No publisher gives the number, so the gap stays open, now
  with a mechanism attached to it.

The +4 / +9 / +16 table is published only for the career-start inheritance; the Classic and Senior April
magnitudes are unpublished, which is what §1.2's arithmetic has been assuming.

### 8.6 Energy cannot be back-solved, and the client has since added a number

`§1.7` and `§6 item 5` are answered. Energy starts at 100 and its maximum is the same
([wikiru, 2026-09-06](https://umamusume.wikiru.jp/)); failure rate "starts to increase when energy is
below roughly half", ceiling 99%, floor 1% even at zero, and higher training level costs more, which is
what pushes an Lv4 tile's risk up. **No source makes Mood an input to the printed failure rate**, and no
publisher documents a band, a colour threshold or the warning string. So 39% is consistent with energy
under 50 and "about a third full" is consistent with that, but the value is not derivable. Record the
percentage as the client prints it.

One thing to go back and look for: the official Global account announced that "the numerical value for
your Umamusume's amount of energy will be displayed under the energy bar"
([~2025-10-24, official](https://x.com/uma_musu_en/status/1983143988082454858)). These frames show no
number under the bar, so either the build predates that change or the readout is elsewhere on the
screen. Worth one capture of the header region.

### 8.7 One calendar inconsistency the pass could not resolve

Sources put the finals at Senior Late December, which is seven half-month turns after Senior Early
October, but the client prints "5 turn(s) until the Unity Cup" on this screen. Either two turns are
consumed by the November event and something else before the finals, or the turn counter uses a rule
these pages do not describe. Unresolved, and it is the client's own number, so the client wins for
planning purposes.

### 8.8 Net effect on the plan in §5

Step 4 changes: buy the white Ignited Spirit GUTS at 130, because it is the published prerequisite for
the gold the November event may pay. Step 3's target changes shape depending on §8.2, and the burst
count on the Team Info panel is the tiebreaker rather than an assumption either way. Everything else in
§5 stands.
