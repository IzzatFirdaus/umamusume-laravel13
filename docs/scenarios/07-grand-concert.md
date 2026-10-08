# Brighter Together Our Grand Concert (Grand Live) — Scenario Guide

**Server:** `[Global]`
**Status:** Sourced guide, refilled 2026-10-05 from a primary read. Was a Known-Gap Stub (2026-09-27).
**Last Verified:** 2026-10-05
**Superseded By:** none (this file supersedes the 2026-09-27 stub, whose boundary it still carries in part)

> **What this file is now.** The fourth `[Global]` scenario had its mechanics extracted on 2026-10-05: both Game8
> pages read to the end of the body, GameTora's mechanics sections re-read from raw HTML, and Cygames' `[Global]`
> notice 899 rendered. That is the bar this file's own stub set for itself ("a Game8 or GameTora scenario page read
> to completion, a `[Global]` notice, or a client capture"), so the owner's 2026-09-27 suspension of extraction is
> discharged by the read it asked for and by the 2026-10-05 instruction that reopened the work.
>
> **What this file still is.** A boundary document as much as a guide. Most of the vocabulary is now official copy:
> `[Global]` notice 905 prints **Performance** and its five types, **Promo Concert**, **Grand Concert**, **lessons**,
> **songs**, **concert techniques**, **Live Performance Expectations** and **Great Success**. What is *not* measured is
> the text on the screens: the two bonus layers, the 23 Song titles and their language, the Lesson button labels, and
> the gauge as displayed. So the boundary stands, narrowed, and the reason is still 0 frames, per
> `docs/research-scratch/DESIGN-CORPUS.md` (SCREENSHOT-MANIFEST source). **D-165 and D-241 therefore still bind
> here**: no scenario-specific chrome may be invented from assumption, and what a guide says is not what a client
> prints. The mechanics below are sourced; the *words* above them are not, and the app may render neither the words
> nor any widget built from them until a capture exists.
>
> **Where the raw read lives.** `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md`, section
> "Our Grand Concert: the 2026-10-05 primary read" (page dates, byte counts, verbatim extraction, and the three
> tables that would not come out of the text layer). The changelog entry is `docs/UMAMUSUME_REFERENCE.md` §2.8; the
> mechanics summary is §2.2.4; the naming divergences are §7 rows 48 to 52.

## Sourced facts

| Fact                                                 | Value                                                                                                                                                                                                                                                                                                                                        | Source and grade                                                                                                               |
| ---------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------ |
| `[JP]` title                                         | つなげ、照らせ、ひかれ。私たちのグランドライブ                                                                                                                                                                                                                                                                                               | `scenarios.json` order 4 `[B]`                                                                                                 |
| `[Global]` title, as the export prints it            | **Brighter Together Our Grand Concert** (`name_en` and `name_en_full`); export `name_en_old` is "Grand Live"                                                                                                                                                                                                                                 | `scenarios.json` order 4 `[B]`                                                                                                 |
| `[Global]` title, as the official notice prints it   | **"Brighter Together! Our Grand Concert."**, with an exclamation mark inside the quotation                                                                                                                                                                                                                                                   | [Notice 899](https://umamusume.com/news/899) `[S]`; kept as conflict row 52, not merged                                        |
| Export label                                         | Grand Live (Grand Concert)                                                                                                                                                                                                                                                                                                                   | `docs/UMAMUSUME_REFERENCE.md` §2.1, §1.6.1                                                                                     |
| `[Global]` live since                                | **2026-07-22 22:00 UTC**, to the hour: the export's `start_en` 1784757600 decodes to that instant and notice 899 opens "As of 10:00 p.m., Jul 22, 2026 (UTC)"                                                                                                                                                                                | `[B]` + `[S]`                                                                                                                  |
| `[JP]` live since                                    | 2022-08-24                                                                                                                                                                                                                                                                                                                                   | `scenarios.json` `start_ja` `[B]`                                                                                              |
| Scenario order                                       | **4** of 14                                                                                                                                                                                                                                                                                                                                  | `scenarios.json` `[B]`                                                                                                         |
| Permanent                                            | Yes: "This is a permanent scenario and will remain accessible even after additional scenarios are released"                                                                                                                                                                                                                                  | Game8 607337 `[A]`                                                                                                             |
| Stat caps (Sp / St / Pw / Gu / Wi)                   | **1600 / 1300 / 1300 / 1500 / 1300**, from `stats` `[400, 100, 100, 300, 100]` over the 1200 base                                                                                                                                                                                                                                            | `[B]`, printed identically by Game8 `[A]` and GameTora `[B]`; §2.1                                                             |
| Speed ceiling                                        | Highest `[Global]` Speed cap in play, above URA Finale's 1400                                                                                                                                                                                                                                                                                | derived from the row above                                                                                                     |
| Hard ceiling                                         | 2000 per stat                                                                                                                                                                                                                                                                                                                                | `scenarios.json` `hard_caps` `[B]`                                                                                             |
| Scenario Link                                        | **5 characters, now named**: Smart Falcon `1046`, Agnes Tachyon `1032`, Silence Suzuka `1002`, Mihono Bourbon `1026`, Light Hello `9008`                                                                                                                                                                                                     | `scenarios.json` `[B]`, corroborated by GameTora `[B]` and Game8 `[A]`                                                         |
| Light Hello's status                                 | An **NPC**, "a new original NPC introduced in the story of Grand Live", and her `9008` id sits in the same band as the trainer NPC Aoi Kiryuin `9004`. She carries two `[Global]` support cards: `10083` `[Event Producer]` R and `30052` `[From the Ground Up]` SSR, both `release_en` 2026-07-22, both type `friend`, printed as **Pal**   | GameTora `[B]` + the committed `characters.c6676539.json` and `support-cards.88dea522.json` exports `[B]` + notice 899 `[S]`   |
| Visual evidence                                      | **0 frames.** Still the largest gap in this file                                                                                                                                                                                                                                                                                             | `docs/research-scratch/DESIGN-CORPUS.md`, SCREENSHOT-MANIFEST source                                                           |

⚠️ **The stub's own correction, stated so it is not silently improved.** This file's gap list used to record "the
five identities are not recorded anywhere in this repository". That was false when it was written: the five names
were already in `config/scenarios.php`, and the same day's earlier pass put them into §2.2.4. It is corrected here,
and it is the reason this file now names a source column on every row.

⚠️ **Naming hazard for any importer.** Three labels circulate for one scenario: the export's `name_en`, the notice's
exclamation-marked sentence, and `[JP]` グランドライブ ("Grand Drive"). **Join on the dataset order (4), the
`start_en` instant or the `url_name` `our-grand-concert`, never on a name string.** Same failure mode §6 documents
for Champions Meeting editions.

## The loop, in order

The scenario is a trainee career with a performance track laid over it. Training pays stats **and** the
scenario's resource. The resource buys **Lessons**. Lessons raise a **Hype** gauge. The gauge decides how the live
events go, and the lives are what the run is building toward.

1. **Train.** Each training turn pays stats plus the scenario's **Performance**, the client's own bare noun with no
   "Point" or "Token" after it ([notice 905](https://umamusume.com/news/905) `[S]`; Game8 writes *Performance Points* and
   GameTora *performance tokens*, and both are additions of their own, row 48). The client names **five types**: **Dance,
   Passion, Vocals, Visuals, and Composure** `[S]`, which is Game8's list exactly and corrects GameTora's fifth
   ("Mental", a rendering of the JP word). GameTora supplies the numbers no official page prints: **200 each** at the
   start of the run and **+50 per successful concert** `[B]`. Friendship training pays **two types** where ordinary training pays
   one, which is the mechanical reason this scenario rewards a friendship deck.
2. **Spend it in the Lesson menu**, which sits between the outing and race buttons on the home menu and is
   **locked until the fifth turn**, when the 「グランドライブ再建計画、開始！」 training event fires. Two kinds of
   Lesson: **Live Techniques** (`ライブテクニック`) and **Songs** (`楽曲`). The list stays static until one is
   completed, then refreshes, so several Lessons can be taken in a single turn, and a Technique does not end the
   turn.
3. **Techniques unlock Songs on a fixed pattern** that resets at every live. GameTora by segment, initial then
   looping: before the first live `1-2-3` / `4-4-2-2`; before the second, third and fourth `2-2-2` / `4-5-2-2`;
   before the finale `2-2-2` / `4-3-2-2`. Game8 prints the same shape per year, `1-2-3-4-4` then `2-2-2-4-5`.
4. **A Lesson pays two layers of reward.** An immediate one (GameTora: Practice Bonus, `習得ボーナス`; stats, skill
   hints, skill points, or energy recovery) and, for Song Lessons, a **Live Bonus** (`ライブボーナス`) that is
   queued and switches on **after the next live**, then persists for the rest of the run and levels up when the
   same bonus appears in another Lesson. Three types exist, with their `[JP]` client strings: Friendship Bonus
   (`友情ボーナス`), Speciality Rate Up (`得意率アップ`), Support Event Chance Up
   (`サポート連続イベント率アップ`). A Lesson you cannot afford changes its confirm button (`習得`) into a reserve
   button (`予約`) and prints 「あとX」 over the training menu.
5. **Songs raise the Hype gauge** (`ライブ期待度`). GameTora: **three Songs fill it**, and Songs granted
   automatically count. A filled gauge always guarantees a **Great Success** (`大成功`).
6. **Four Promotional Lives, every six months from late December of Junior Year**, then the Grand Live / Grand
   Concert in Senior Year. Each live pays **5 skill points per Technique Lesson and 25 per Song Lesson** taken
   since the previous live, raises the resource cap by **50**, and a Great Success **raises stat caps**. The
   outcome of the Promo Lives feeds the finale. Lessons may be taken immediately before a live, the way skills are
   learned before a race.
7. **A race career underneath.** Unlike Trackblazer, this scenario "returns the standard trainee career features of
   race goals and career events", including New Year events, the hot spring lottery and per-character hidden
   events, so a Runaway-style gate is reachable here. Character-specific secret events are explicitly back.

## Songs: 23 of them, and no new ones after the third live

Both publishers reach **23** the same way: 21 purchasable in Lessons, plus `Make Debut!` granted automatically
after four turns, plus `GIRLS' LEGEND U` at the finale. Their effect text and cost figures agree row for row; they
disagree on the language of the title, and **neither column is a measured `[Global]` client string** (row 49). Cost
figures are printed as a sequence of numbers per Song; which type each figure belongs to is not recoverable from
either page's text layer, so no figure below names a type.

| Availability         | Game8 `[A]`, English               | GameTora `[B]`, romaji JP              | Effect pair, both pages                                      | Cost figures as printed                      |
| -------------------- | ---------------------------------- | -------------------------------------- | ------------------------------------------------------------ | -------------------------------------------- |
| From the start       | Believe in Miracles!               | Kiseki wo Shinjite!                    | Training Wit Gain +1 / Speciality Rate Up +5                 | 21, 21                                       |
| From the start       | Zero Is Where the Center Stands!   | Tachiichi zero-ban! Juni wa Ichiban!   | Training Speed Gain +1 / Support Event Chance Up +1          | 21, 21                                       |
| From the start       | Getaway! Fallin' Love              | Nigekiri! Fallin' Love                 | Training Guts Gain +1 / Support Event Chance Up +1           | 21, 21                                       |
| From the start       | Go This Way                        | Go This Way                            | Training Power Gain +1 / Support Event Chance Up +1          | 21, 21                                       |
| From the start       | Ring Ring Diary                    | Ring Ring Diary                        | Training Stamina Gain +1 / Support Event Chance Up +1        | 21, 21                                       |
| From the start       | Full Speed Ahead! Umadol Power☆    | Zensoku! Zenshin! Umadol Power☆        | Speed +22 / Friendship Bonus +5%                             | 32, 12                                       |
| From the start       | Here Comes Our Time                | Seishun ga Matteru                     | Power +22 / Friendship Bonus +5%                             | 32, 12                                       |
| From the start       | Run n' Run!                        | RUN×RUN!                               | Skill Points +22 / Friendship Bonus +5%                      | 14, 16, 14                                   |
| After 4 turns        | Make Debut!                        | Make debut!                            | All of the resource +10 / Speciality Rate Up +5              | automatic                                    |
| After the 1st live   | Hey, Guess What!                   | A・NO・NE                              | Training Guts Gain +2 / Speciality Rate Up +5                | 42, 21                                       |
| After the 1st live   | Our Blue Bird Days                 | Bokura no Bluebird Days                | Training Speed Gain +2 / Speciality Rate Up +5               | 21, 42                                       |
| After the 1st live   | Run for Our Dream!                 | Yume wo Kakeru!                        | Training Skill Point Bonus +2 / Speciality Rate Up +5        | 21, 21                                       |
| After the 2nd live   | Grow Up and Shine!                 | Grow Up, Shine!                        | Training Skill Point Bonus +3 / Support Event Chance Up +1   | 21, 21, 21                                   |
| After the 2nd live   | Hoppity Sunny Days ♪               | Pyoitto ♪ Hallelujah!                  | Training Stamina Gain +2 / Speciality Rate Up +5             | 42, 21                                       |
| After the 2nd live   | Seven Colors Scenery               | Nanairo no Keshiki                     | Training Power Gain +2 / Speciality Rate Up +5               | 21, 42                                       |
| After the 2nd live   | Sunbeam Cheer                      | Komorebi no Yell                       | Training Wit Gain +2 / Support Event Chance Up +1            | 42, 21                                       |
| After the 3rd live   | Dream Sky                          | Yumezora                               | Wit +22 / Friendship Bonus +5%                               | 22, 22                                       |
| After the 3rd live   | Present March ♪                    | PRESENT MARCH♪                         | Power +22 / Friendship Bonus +5%                             | 22, 22                                       |
| After the 3rd live   | Precious Treasure Box              | Daisuki no Takarabako                  | Speed +26 / Friendship Bonus +10%                            | 42, 26                                       |
| After the 3rd live   | The World's at Our Whim            | Sekai wa Bokura no Iinari Sa           | Stamina +22 / Friendship Bonus +5%                           | 32, 12                                       |
| After the 3rd live   | Sky-Blue Spring                    | Harusora BLUE                          | Guts +22 / Friendship Bonus +5%                              | 12, 32                                       |
| After the 3rd live   | Fanfare for the Future!            | Fanfare for Future!                    | Guts +26 / Friendship Bonus +10%                             | 26, 42                                       |
| Finale               | Girls' Legend U                    | GIRLS' LEGEND U                        | All stats +10 / Friendship Bonus +10%                        | not a Lesson; special version at 18+ Songs   |

GameTora prints the all-Songs cost as five totals, **252 / 201 / 150 / 275 / 196**, one per resource type, and does
not print the column labels in text, so the totals are recorded without type attribution.

## Objectives the scenario itself sets

Both thresholds are printed by both pages, with different amounts of detail.

- **16 Songs by early November of Senior Year** fires the scenario-link event. Game8 names it **Closer Together**;
  GameTora quotes the `[JP]` client title 「あなたと私を繋げるライブ」. Five lyric lines are offered; four are
  attached to a scenario link character, and choosing one of those while training that character or holding one of
  her cards pays the rare hint, otherwise the common one. Every skill below resolves by id in the committed
  `[Global]` skill export.

  | Lyric line | Rare hint | Common fallback |
  | --- | --- | --- |
  | "A song with some call-and-response"... | Full Speed! `202281` | Full Tilt `202282` |
  | "Gratitude towards the fans, without whom I would not be running"... | Concentration `200431` | Focus `200432` |
  | "I'm home"... | Trackblazer `200711` | Rosy Outlook `200712` |
  | "The power to achieve a breakthrough"... | Come What May `201701` | All I've Got `201702` |
  | "Song brings us closer together"... | Lane Legerdemain `200501` | not printed |

  ⚠️ The third row is a **skill named Trackblazer** (`切り開く者`). It is not the third scenario and nothing may
  join on that string.
- **18 Songs by early December of Senior Year** pays the scenario skill **I Wanna Win with You** `210071` (the
  export's own spelling, lowercase `with`; Game8 prints "I Wanna Win With You"). Fewer than 18 pays its common
  version instead. `GIRLS' LEGEND U` never counts as the eighteenth, and `Make Debut!` does. GameTora adds that all
  22 purchasable Songs lift the hint to Lv 3 and that 18 to 21 pays a Lv 1 hint, and it renders the gold-and-common
  names only in an image table, so the band-to-skill mapping is taken from Game8's prose.
- **18 or more Songs also changes the finale's show:** a special version of `GIRLS' LEGEND U` plays and the Hype
  gauge starts displaying `Special Hype!`; with 17 or fewer the normal version plays. The special version then enters
  the Live Theater.
- **Fan count is not a strict unlock condition and not every live needs to be a Great Success** — Game8 prints both,
  with its own caveat that the lines come from JP experience.

## Training values, facility levels and the fan gates

Facility leveling is **URA's repetition rule**: level 1 to 5, one level per four uses of the same discipline
(GameTora, tier B, single page). `config/scenarios.php` reads `facility_level_source => 'repetition'` from that.

Base values at facility level 1, no support present, no character growth (GameTora's table): Speed `+8 Speed,
+4 Power, +4 SP`, 10 of the resource, `−19` energy; Stamina `+8 Stamina, +6 Guts, +4 SP`, 10, `−20`; Power
`+4 Stamina, +9 Power, +4 SP`, 10, `−20`; Guts `+2 Speed, +2 Power, +7 Guts, +4 SP`, 10, `−20`; Wit `+2 Speed,
+6 Wit, +5 SP` plus one figure whose column the text layer does not resolve. **Tier B, one page, not corroborated.**

Unique-skill level-ups use the **URA fan gates**: 60,000 by Valentine's Day, 70,000 by early April **plus a
three-bar friendship with Director Akikawa**, 120,000 by late December of Senior Year; a dirt-leaning trainee
instead needs 40,000 / 60,000 / 80,000. This closes the row the stub marked "the event-gate thresholds for this
scenario are ❌ unknown". The resource's own cap rises by 50 at every successful live, which is the only escalation
the pages publish.

## Inheritance raises the caps

Unique to this scenario among the four `[Global]` entries, on GameTora's word (tier B, single page):

- Blue Sparks uncap their stat at the start of the run by **4 at one star, 9 at two, 16 at three**, printed as a
  maximum of **48 uncapped points** from one parent set.
- Green unique-skill Sparks now contribute uncaps as well as a hint, keyed to the growth bonuses of the character
  they come from (worked example: Vodka's `Cutting × DRIVE!` gives Speed and Power uncaps, matching her 10% Speed
  and 20% Power bonuses).
- The amounts paid during inheritance events are randomised and **no range is published**, so the tracker takes the
  rule and never a number.
- The scenario Spark is **Our Grand Concert**, factor id `3000401`, Speed plus Guts. Game8's prose and the committed
  `static_scenarios.json` row agree on both the name and the two effects; GameTora's prose calls it "Grand Live
  Scenario", which is its rendering of the `[JP]` string. Follow the export (row 50).

## ❌ UNVERIFIED — the boundary that remains

Not measured, not inferred, and not to be rendered:

- **The screen text, a short list now.** The two bonus layer names (GameTora's Practice Bonus and Live Bonus against
  Game8's collapsed "Mastery Bonus"), the 23 Song titles and their language, the Lesson confirm and reserve button
  labels, and the gauge as rendered. The resource noun, its five type names, **Promo Concert**, **Grand Concert**,
  **lessons**, **songs**, **concert techniques** and **Live Performance Expectations** left this list on 2026-10-05,
  when notice 905 was read: they are Cygames' English. Rows 48 and 52 closed; rows 49 and 51 stay open.
- **No per-level magnitude anywhere.** No Lesson cost curve, no Hype value per Song, no Live Bonus percentage
  ladder, no published range for the inheritance uncaps. A "level 5 Friendship Bonus" is a level the pages say
  exists and never quantify.
- **Three GameTora tables are images.** The facility-to-resource-type table (recovered only as icon file keys, and
  labelled an inference in the master: Speed Dance/Visual, Stamina Passion/Vocal, Power Vocal/Mental, Guts
  Visual/Dance, Wit Mental/Passion), the per-type all-Song cost totals, and the gold-and-common banding in its
  Skills table.
- **The turn shape of the live events.** Which turns are consumed, whether a live ends a turn, and what the
  backstage menu offers beyond the Lesson step are not published as a turn list, so this file offers no
  per-turn script the way `02-unity-cup.md` does.
- **Whether the Song titles are localised on `[Global]` at all.** Row 49, and it is the same single capture away as
  the rest.
- **No deck or strategy claim in this file is presented as a mechanic.** The published deck shape (3 Speed / 2 Wit
  / 1 Pal, or 2 / 2 / 1 plus one free, 1 Stamina for Medium and Long), the "Light Hello SSR is a must-have" advice,
  the priority-Song list and the "stop taking Techniques after the fourth Song" tip are publisher guidance at tier A
  and B. The tool records what the Trainer did; it does not recommend.

## Matrix mapping (§10n) — what the dashboard must do today

The composition matrix in `docs/research-scratch/DESIGN-CORPUS.md` (CONSTRAINTS section) still renders this
scenario's column from the baseline, and `config/scenarios.php` still turns every panel off. That is now a
**decision under review, not an absence of information**: the mechanics are on file, and none of them has a
component.

**Ruled 2026-10-08.** The owner answered the question this paragraph raised: **Our Grand Concert is to gain
its own surfaces as part of Trainer Desk 2.0.** The ruling authorises the scenario experience and nothing
else: it is not authority to invent game data or client-facing strings, so the `❌ UNVERIFIED` boundary above
stays authoritative, and a surface that needs a value no source publishes is left unbuilt rather than filled
with a plausible number. The table below therefore describes the state before that work starts, not a
position anyone needs to re-argue. `config/scenarios.php` records the same ruling on the scenario's entry.

| Widget                                                                                                     | Our Grand Concert, today                                                                                                                                                                                                                                                                              |
| ---------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Turn chip, trainee and scenario identity, Energy gauge, Mood tier, stat band, timeline                     | **present**                                                                                                                                                                                                                                                                                           |
| Resource strip                                                                                             | **baseline only** — turn, trainee, scenario, Energy, Fans                                                                                                                                                                                                                                             |
| Stat cap stack                                                                                             | **present**, 1600 / 1300 / 1300 / 1500 / 1300 over the 1200 base                                                                                                                                                                                                                                      |
| Fan readout                                                                                                | **present**, and the gate thresholds are now published (60k / 70k / 120k, dirt-leaning 40k / 60k / 80k), so a "next gate" value is no longer barred by silence. It is still barred by scope until a surface is specified                                                                              |
| Race Calendar                                                                                              | **off.** The scenario has character race goals, which is what the panel is for, so this cell is the one the read genuinely unsettles. It is an owner decision, not a fact to flip                                                                                                                     |
| Grade Point meter, Team Rank gauge, Spirit Burst roster, Shop, epithets, Rival marker, Race Fatigue chip   | **off, and correctly so.** None exists here                                                                                                                                                                                                                                                           |
| A resource chip, a Hype gauge or a live marker                                                             | **not built, and not to be built from this file.** No component, no client string, and the tests that hold the baseline strip for this scenario (`ResourceStripTest`, `RaceCalendarTest`, `GradePointMeterTest`, `GuidedStepScenarioVariationTest`, `GoalPanelsOnRunDetailTest`) assert the absence   |

**One consequence the owner has to settle, recorded rather than decided here.** D-241 and gate G-41 name this
scenario's column as the acceptance case for "an undescribed scenario renders as baseline plus known caps". The
scenario is no longer undescribed, so the *wording* of that rule now points at a case that does not exist, while
the behaviour it tests is unchanged and still asserted. `config/scenarios.php`'s `documented` flag moved to match
the file it describes; the gate registry's own text is the owner's to amend, and nothing in this pass edits it.

## What would close the rest

In priority order, each one cheap, and the first two are one browser session:

1. **Capture the Lesson list screen.** Row 48 closed from the launch notice, so what this frame now owes is narrower
   and it is still the single best capture in the file: the bonus-layer labels as printed, the Song titles in whatever
   language the client carries them (row 49), and the confirm and reserve button text.
2. **Capture the pre-live screen** (the backstage menu with the gauge and the information button GameTora describes
   as a round "i"), for the gauge label and the live's own wording, plus the scenario-select screen for the title
   string in row 52.
3. **Then update** §2.2.4, §7 rows 48 to 52 and the `lang/en/uma.php` terms map with measured strings, and record
   the frames in the manifest so this row stops reading 0.
4. **Not needed:** another guide. GameTora, Game8 and uma.guide now agree on the shape, and a fourth paraphrase
   would not close anything the frames do not.

Compiled 2026-10-05. Every row above carries a source and a grade; every item in the `❌` list is marked unverified
rather than absent, and no mechanic in this file was inferred from `[JP]` prose, from another scenario, or from the
scenario's name.
