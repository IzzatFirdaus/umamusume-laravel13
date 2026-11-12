# Scenario research sources

**Phase 0 deliverable 6 of 6.** The source register every other document in this directory cites.

Every row carries the URL that was actually fetched, the publisher, the page's own last-updated date as read
off the page, and the region the figures describe. A figure whose source is not in this table does not appear
as a fact anywhere in this directory; it appears in `scenario-information-gaps.md` instead.

**Fetch date for the whole set: 2026-11-12.** That is when every URL below was read. It is not the pages' own
publication dates, which are given per row and differ.

**Tier.** `A` = the publisher (Cygames, its official Global site, or the client's own screen text captured in a
screenshot). `B` = a maintained reference wiki or database (umamusu.wiki, GameTora). `C` = a community guide
(Game8, uma.guide, Mihono Studios, GameObserver). A `C` row is evidence that a guide says something, never
evidence that the game does.

---

## 1. Publisher sources (tier A)

| # | URL | Publisher | Page | Date on page | Region | What it supports |
|---|---|---|---|---|---|---|
| A1 | <https://umamusume.com/news/1029> | Cygames, official Global site | "Check out all the latest updates!" | posted 2026-09-06 22:00 UTC | Global | The client's own scenario name `The Beginning! URA Finale!`; the admission that a URA Finale rework happened |
| A2 | <https://umamusume.com/news/100100> | Cygames, official Global site | "Issue with Independent Training" | posted 2026-08-13, updated 2026-08-19 | Global | All four Global career scenario names in one list: `The Beginning! URA Finale!`, `Unity Cup: Shine On, Team Spirit!`, `Brighter Together: Our Grand Concert`. Note Trackblazer is absent from this notice, which is a fact about the notice |
| A3 | <https://umamusume.com/news/956> | Cygames, official Global site | "The event 'Trainer Aptitude Test' is here!" | posted 2026-08-12 22:00 UTC | Global | Names `Brighter Together! Our Grand Concert` with the exclamation mark; establishes the scenario was event-eligible by August 2026 |
| A4 | <https://umamusume.com/news/200007> | Cygames, official Global site | "Trackblazer: Start of the Climax Release Support Event" | posted 2026-03-09 02:00 UTC | Global | Trackblazer announced for Global before 2026-03-12 |
| A5 | <https://umamusume.com/news/200008> | Cygames, official Global site | "Trackblazer: Start of the Climax Celebration Event" | posted 2026-03-13 02:00 UTC | Global | Trackblazer live on Global as of 2026-03-13; the client name is `Trackblazer: Start of the Climax` |
| A6 | <https://umamusume.com/news/200012> | Cygames, official Global site | "Brighter Together: Our Grand Concert Release Support Event" | posted 2026-07-20 01:55 UTC | Global | Grand Concert announced for Global in the week before 2026-07-22 |
| A7 | <https://umamusume.com/news/200013> | Cygames, official Global site | "Brighter Together: Our Grand Concert Celebration Event" | posted 2026-07-23 01:55 UTC | Global | Grand Concert live on Global by 2026-07-23 |
| A8 | <https://umamusume.com/news/200014> | Cygames, official Global site | "Brighter Together: Our Grand Concert Support Event" | posted 2026-07-27 01:55 UTC | Global | The scenario's own English title reproduced a third time |
| A9 | <https://umamusume.com/news/155> | Cygames, official Global site | "List of Known Issues" | posted 2025-06-26, updated 2026-07-31 | Global | Two Trackblazer client defects, and the client name `Trackblazer: Start of the Climax` used in an error report |
| A10 | <https://umamusume.com/news/1122> | Cygames, official Global site | "New Beginnings Part 1 now available!" | posted 2026-10-20 22:00 UTC | Global | "Increased Hot Spring Ticket rates do not apply to the Career Scenario Trackblazer: Start of the Climax, as it does not feature the Career event 'Raffle Time!'" — a publisher statement that a named career event is **absent** from Trackblazer |
| A11 | <https://umamusume.gg/the-new-career-scenario-trackblazer-start-of-the-climax-is-here> | **DotGG, a third-party news site** — it reproduces Cygames' launch announcement verbatim and carries its own `DotGG` masthead | "The new Career Scenario 'Trackblazer: Start of the Climax' is here!" | 2026-03-12 | Global | Result Points (the term and the reset rule), the yearly point target, Twinkle Star Climax and its Victory Points, Pro Shop and Shop Coins, Rivals, the Best Umamusume Award. **Tier-A wording on a tier-C host** — see the note below |
| A12 | <https://umamusume.gg/the-new-career-scenario-brighter-together-our-grand-concert-is-here> | **DotGG, a third-party news site** | Grand Concert announcement | 2026-07-22 | Global | Not reachable at fetch time; retained so a later pass does not re-spend the fetch. Same host caveat as A11 |
| A14 | <https://github.com/steve1316/uma-android-automation> (`bot/campaigns/GrandLive*.kt`) | An open-source Android automation bot for the Global client — **not a publisher** | Three Kotlin files, `GrandLive.kt`, `GrandLiveLessons.kt`, `GrandLiveTraining.kt` | undated in the fetched files; current 2026 | Global | **71 client strings transcribed from the running game**, because an automation bot has to match the client's own text to drive it. Used only to corroborate strings the captured frames (A13) already show, and to extend them where the frames cover three songs and the code covers all 23 |
| A13 | Client screenshots, Global build — see §4 | Cygames, captured by GameObserver | Lesson screen, Performance Points tutorial, Pro Shop tutorial | captured on or before 2026-07-31 | Global | The **only** tier-A evidence for the client's own on-screen wording in Grand Concert. Every string in §5 is quoted from these frames |

### 1.1 The `umamusume.gg` host caveat, and what it costs the Trackblazer term

**`umamusume.gg` is not Cygames.** It is DotGG, a third-party gaming news site: the page carries its own
`DotGG` masthead and an "Articles: 316" byline, and its only contact with Cygames is that it reproduces the
publisher's launch announcement. The page's **text** reads as publisher copy ("the new Career Scenario ...
has been added to the game", "For more information ... please refer to ... under "Career" in Help"), which
is why it was first filed here as tier A. **That tier was wrong and is corrected above.**

What this costs:

| Claim | Standing after the correction |
|---|---|
| Trackblazer's launch date, 2026-03-12 | **Unchanged.** A5 (`umamusume.com/news/200008`, 2026-03-13, a real Cygames notice) brackets it, and umamusu.wiki agrees |
| Trackblazer's client name `Trackblazer: Start of the Climax` | **Unchanged.** A5, A9 and A10 all carry it on `umamusume.com` |
| `Twinkle Star Climax` as the finals' name | **Unchanged.** B2's heading and body carry it, and the name is not what this host supplies |
| `Result Points` as the currency's term | **Weakened from A to B.** The only page stating it in the publisher's voice is DotGG's reproduction. umamusu.wiki (B2) uses `Result Points` in its body sentence too, so the term still has two independent sources — but **zero of them is on `cygames`-owned infrastructure**, and that is the honest standing |

**Why the term still wins anyway.** Two independent sources use it, the app-side alias is documented rather
than denied, and the alternative — `Grade Points` — has the same evidential weight (uma.guide, one page). A
reference document should print the word two sources agree on and record that neither is the publisher's own
page, which is exactly what §2.2.3 and the reference guide's row 60 now do.

**The three notice clusters, and why they are rows rather than one row each.** A4, A6 and A8 are the
*announcement* half of each scenario's launch (a support event posted days before), and A5, A7 and A9 are the
*celebration* half (posted after). A pair is what dates a launch to a day, because the announcement carries the
pre-launch week and the celebration carries the post-launch one. A12 is the official scenario page the Grand
Concert notices point at; it was not reachable at fetch time, and it is a row so a later pass does not spend the
fetch again trying to find a page that this pass already tried.

**Why notices A1–A11 matter more than they look.** The repository's own `docs/scenarios/03-trackblazer.md` was
written pre-release and marks itself `Superseded`. The official Global launch page (A11) is the publisher's own
description of the shipped scenario, and it is the source that settles `Result Points` as the client's term
rather than `Grade Points`. Every prior in-repo document uses `Grade Points`. See
`scenario-information-gaps.md` §3 for that conflict and how the test cases resolve it.

---

## 2. Reference-wiki and database sources (tier B)

| # | URL | Publisher | Page | Date on page | Region | What it supports |
|---|---|---|---|---|---|---|
| B1 | <https://umamusu.wiki/Game:The_Beginning:_URA_Finale> | umamusu.wiki | "Game:The Beginning: URA Finale" | last edited 2026-11-03 | Global + JP | URA Finale caps at 1400 ×5; the scenario-link character; Happy Meek duel reward table by level; the URA Finale three-race ladder and its turn numbers; the 41 possible finals course combinations; the fan gates; the per-scenario record awards |
| B2 | <https://umamusu.wiki/Game:Trackblazer:_Start_of_the_Climax> | umamusu.wiki | "Game:Trackblazer: Start of the Climax" | last edited 2026-10-11 | Global + JP | Caps 1200/1900/1200/1200/1500; Result Points by race grade; Shop Coins by placement; the six-turn Pro Shop reset and the three-turn win refresh; Rivals; Twinkle Star Climax Victory Points; the full Trackblazer epithet table; the fixed scenario-event table with its bond and fan conditions |
| B3 | <https://umamusu.wiki/Game:Brighter_Together!_Our_Grand_Concert> | umamusu.wiki | "Game:Brighter Together! Our Grand Concert" | last edited 2026-11-03 | Global + JP | Caps 1600/1300/1300/1500/1300; the five Performance Tokens and their facility distribution; Lessons and the two bonus layers; the technique-then-song unlock pattern per concert stretch; the 23 songs with per-type costs; the five concerts and Great Success; the 16- and 18-song thresholds; the scenario links |
| B4 | <https://umamusu.wiki/Game:Unity_Cup:_Shine_On,_Team_Spirit!> | umamusu.wiki | "Game:Unity Cup: Shine On, Team Spirit!" | last edited 2026-10-11 | Global + JP | Unity Cup caps at 1300 ×4 / Wit 1800; the five-race team schedule and the preseason structure; the team-name table; the finals |
| B5 | <https://umamusu.wiki/Game:Career_Mode> | umamusu.wiki | "Game:Career Mode" | last edited 2026-11-03 | Global + JP | Career-length and turn-count frame for all scenarios; the shared career shell the four scenarios sit inside |
| B6 | <https://gametora.com/umamusume/trackblazer> | GameTora | Trackblazer scenario reference | page last updated 2026-03-12 | Global | Trackblazer mechanics as first published on the Global launch day; corroborates the Result Point and Shop Coin tables |
| B7 | <https://gametora.com/umamusume/our-grand-concert> | GameTora | Grand Concert scenario reference | page updated 2026-07-26 | Global | Grand Concert mechanics; the facility-to-token-type table that survives in this page only as icons; the "Mental" rendering of the fifth type, which is a GameTora word and not the client's |

---

## 3. Community-guide sources (tier C)

These are cited for mechanics no publisher page states, and for the strategy framing that a test case needs in
order to be *representative* rather than arbitrary. No tier-C figure is presented as a client fact.

| # | URL | Publisher | Page | Date on page | Region | What it supports |
|---|---|---|---|---|---|---|
| C1 | <https://mihonostudios.com/scenarios/ura-finale> | Uma Musume Hub (Mihono Studios) | "URA Finale guide: Happy Meek duels, calendar, finals and strategy" | undated on page; carries the Global in-game Help text verbatim | Global | The in-game Help text for URA Finale and the Happy Meek duels, reproduced in both English and Japanese; the fixed-event calendar with turn positions; the duel reward table by level; the finals rewards; the end-of-story event table |
| C2 | <https://mihonostudios.com/scenarios/grand-concert> | Uma Musume Hub (Mihono Studios) | "Grand Concert guide: performance points, lessons, songs and concerts" | undated on page; carries the Global in-game Help text verbatim | Global | The nine Grand Concert Help pages reproduced verbatim in English and Japanese; the fixed moment list with turn positions; the lesson catalogue by type; the per-facility token table with its community-measured percentages; the song list with costs and both bonus layers |
| C3 | <https://game8.co/games/Umamusume-Pretty-Derby/archives/580723> | Game8 | "Trackblazer (Make a New Track) Scenario Guide" | last updated 2026-08-25 02:16 | Global | Trackblazer strategy; the scenario's own name for the open career; the Pro Shop framing the repository already quotes in `config/scenarios.php` |
| C4 | <https://game8.co/games/Umamusume-Pretty-Derby/archives/536350> | Game8 | "Career Scenario Mode Guide" | last updated 2026-07-24 10:16 | Global | All four scenarios' stat caps in one table, which is the cross-check the repository's `config/scenarios.php` needed for OGC |
| C5 | <https://game8.co/games/Umamusume-Pretty-Derby/archives/607337> | Game8 | "Grand Live (Grand Concert) Scenario Guide" | updated 2026-07-26 | Global | Grand Concert recommended use, planning focus, and the deck shape the repository already quotes |
| C6 | <https://uma.guide/guides/trackblazer> | uma.guide | "Trackblazer Guide (MANT)" | last updated 2026-04-29 | Global | Result Point deadlines, Shop Coin table, race-fatigue odds, Pro Shop item worth ratings, Twinkle Star Climax preparation, the year-end fan-and-bond gates |
| C7 | <https://uma.guide/guides/grand-concert> | uma.guide | Grand Concert community guide | undated at fetch | Global | The song and lesson community reading, used only where C2 and B3 agree |
| C8 | <https://gameobserver.com/umamusume-pretty-derby-grand-concert-scenario-guide> | GameObserver | "Umamusume: Pretty Derby – Our Grand Concert Beginner's Guide" | published 2026-07-31 | Global | Its prose corroborates the loop, and its **captured Global client frames** are the tier-A evidence in A13 |

---

## 4. The captured Global frames and what they are

Five frames were downloaded from the GameObserver article (C8) and read one at a time. They are the first
Global client captures of the Grand Concert screens this repository has ever held; `docs/scenarios/07-grand-concert.md`
records "0 frames" as its largest gap and its `❌ UNVERIFIED` list as unmeasured screen text.

| Frame (session path) | Screen | What it settles |
|---|---|---|
| `go_perfpoints.jpg` | Performance Points tutorial | The label `Performance Points`; the five type names with their abbreviations `Da / Pa / Vo / Vi / Co` |
| `go_lessons.jpg` | Lessons | `Lessons` title, `Select a technique or song to learn.`, technique cards with `Performance Point Cost`, `Learnable!`, `Songs Learned`, `Full Stats`, `Concert Info` |
| `go_songs.jpg` (read as the songs tab) | Lessons, song cards | Song titles in English (`Believe in Miracles!`, `Go This Way`, `Full Speed Ahead! Umadol Power☆`), the two bonus rows on a song card, `Performance Point Cost` |
| `go_seeyalater.jpg` | Recreation event | The `See Ya Later!` skill acquisition path |
| `go_main.jpg` | Concert stage | A concert scene, no HUD; it settles nothing textual |

**These frames are session-local evidence and they are not committed into the repository.** They sit at
`.scratch-uma/phase0-scenario-research/raw/img3/`, which is gitignored. A later pass that needs to re-check a
quoted string must either re-fetch C8 or commit the frames under `docs/audits/`. This document quotes them so
the strings survive the scratch cleanup; the frames themselves do not.

### 4.1 The automation-codebase source, and why it is not a publisher

A14 is an open-source automation bot. It is **not** a publisher and it is **not** tier A in the same sense as a
Cygames page: it is a third party's transcription. It earns a place in this register for one specific reason —
**a bot that cannot match the client's exact on-screen text cannot drive the client**, so its string table is a
maintained, testable copy of what the screen says. That makes it stronger evidence than a guide's prose and
weaker than a frame.

**What it adds over the frames:** all **23** song titles in English, where the frames show three; the
`Not enough performance points` string the reserve flow needs; `Learn` and `Learnable!` as distinct states; the
`Skill Hint Lvl +1 / +2 / +3` ladder; the `Stat Gains` / `Skill Hints` / `Support Events` / `Totals` group
headings on a lesson card; and the `Skip the Grand Concert cutscene` control.

**What it does not establish:** any figure. It is a string table, so it gives words and no numbers. Every
magnitude in this directory still comes from B3, C2 or the frames.

**One caution recorded.** The bot's table contains `"Jur Blue Bird Days"` and `"So This Way"` beside
`"Our Blue Bird Days"` and `"Go This Way"`. Those look like OCR-tolerant fallbacks or partial matches rather than
client strings, so **no surface may print them**. The 23 canonical titles are the ones §5 lists.

**One more frame set** is drawn from GameTora (`raw/img/gt_expectation_gauge.png`), and it is **Japanese**:
`ライブ期待度` (live expectation gauge) and `ますますの盛り上がり`. It is cited here precisely because it is the
counter-example — a gauge label read off a JP frame must never be printed as a Global string. The Global frame
`ug_gc_ui_hype_level.webp` shows the same gauge labelled `Hype Level` with the state word `Mild Hype`.

---

## 5. Strings the captured Global frames give, verbatim

Each string below is quoted from a frame named in §4. These are the strings `docs/scenarios/07-grand-concert.md`
records as unmeasured, and they are the strings a Grand Concert surface in Trainer Desk may print.

| String | Frame | Where it appears |
|---|---|---|
| `Performance Points` | `go_perfpoints.jpg`, `go_songs.jpg` | The resource's panel header |
| `Dance` `Passion` `Vocals` `Visuals` `Composure` | `go_perfpoints.jpg` | The five type names, spelled out |
| `Da` `Pa` `Vo` `Vi` `Co` | `go_perfpoints.jpg`, `go_lessons.jpg` | The five abbreviations, used on every cost strip |
| `Train successfully to gain performance points!` | `go_perfpoints.jpg` | Tutorial headline |
| `There are five different types of performance points that you can acquire through training! The performance type in which you can acquire points through each training session will differ each turn.` | `go_perfpoints.jpg` | Tutorial body, verbatim |
| `Lessons` | `go_lessons.jpg`, `go_songs.jpg` | Screen title |
| `Select a technique or song to learn.` | `go_lessons.jpg`, `go_songs.jpg` | Instruction line |
| `Technique` | `go_lessons.jpg` | Card type pill on a technique card |
| `Songs` | `go_songs.jpg` | Card type pill on a song card |
| `Performance Point Cost` | `go_lessons.jpg`, `go_songs.jpg` | Cost strip heading |
| `Learnable!` | `go_lessons.jpg` | State pill when the cost is met |
| `Skill Hint Lvl +3 (Mile)` | `go_lessons.jpg` | Group Lesson Advanced's effect line |
| `Vocal Training Advanced Class`, `Yoga Intermediate Class`, `Group Lesson Advanced` | `go_lessons.jpg` | Technique names |
| **All 23 song titles in English** — see the register's §5A | A13 (three of them) + A14 (all 23) | The song list, in the client's own language |
| `Training Wit Gain +1`, `Training Power Gain +1`, `Speed +22` | `go_songs.jpg` | The mastery-bonus row on a song card |
| `Specialty Priority +5`, `Support Chain Event Frequency Lvl +1`, `Friendship Training Effectiveness +5%` | `go_songs.jpg` | The concert-bonus row on a song card |
| `Hype Level` | `ug_gc_ui_hype_level.webp` | Gauge banner |
| `Mild Hype` | `ug_gc_ui_hype_level.webp` | Gauge state word |
| `Full Stats`, `Concert Info`, `Songs Learned`, `Log`, `Quick`, `Skip` | `go_lessons.jpg`, `go_songs.jpg` | Bottom and side controls |
| `Not enough performance points` | A14 | The refusal when a lesson's cost is unmet — the reserve flow's entry point |
| `Learn`, `Learnable!`, `Locked` | A14 (first two corroborated by `go_lessons.jpg`) | The three card states |
| `Stat Gains`, `Skill Hints`, `Support Events`, `Totals` | A14 | The group headings on a lesson card |
| `Skill Hint Lvl +1`, `+2`, `+3` | A14 (`+3` corroborated by `go_lessons.jpg`) | The hint-level ladder |
| `Training Gain`, `Training Wit Gain +2`, `Training Guts Gain +1`, `Training Skill Pt Gain +2`, `+3` | A14 (`Training Wit Gain +1` and `Training Power Gain +1` corroborated by `go_songs.jpg`) | The mastery-bonus wording per stat |
| `Training Effectiveness`, `Friendship Training Effectiveness` | A14 (`Friendship Training Effectiveness +5%` corroborated by `go_songs.jpg`) | The concert-bonus wording |
| `Skip the Grand Concert cutscene` | A14 | The finale's control |
| `Group Lesson Basics`, `Group Lesson Intermediate Class` | A14 (`Group Lesson Advanced` corroborated by `go_lessons.jpg`) | The three group-lesson tiers |

**Two conflicts these frames create, recorded not resolved silently:**

1. **`Vocals` / `Visuals` plural, `Composure` for the fifth.** The client writes `Vocals` and `Visuals` with a
   trailing `s`, and `Composure` where GameTora writes `Mental`. `app/Enums/PerformanceType.php` already carries
   exactly these five spellings and its docblock cites notice 905 for them. **The frames confirm the enum; they
   do not contradict it.** This is the one place where a repo claim is upgraded from "notice 905 says" to
   "the client screen says".
2. **`Specialty Priority` vs `Speciality Rate Up`.** The client's song card prints `Specialty Priority +5`. The
   repository's `docs/scenarios/07-grand-concert.md` records `Speciality Rate Up (得意率アップ)` from GameTora.
   Those are different words for the same bonus, and the client's is the one a surface may print.

---

## 5A. The 23 song titles, in the client's English

A13 shows three of these on a song card. **A14 carries all 23**, and the two agree wherever they overlap, which is
what makes the list usable. Availability windows and the cost figures are B3's and C2's; the **titles** here are
the client's.

| # | Title | Availability |
|---|---|---|
| 1 | `Believe in Miracles!` | From the start |
| 2 | `Zero Is Where the Center Stands!` | From the start |
| 3 | `Getaway! Fallin' Love` | From the start |
| 4 | `Go This Way` | From the start |
| 5 | `Ring Ring Diary` | From the start |
| 6 | `Full Speed Ahead! Umadol Power☆` | From the start |
| 7 | `Here Comes Our Time` | From the start |
| 8 | `Run n' Run!` | From the start |
| 9 | `Make Debut!` | Automatic after four turns |
| 10 | `Hey, Guess What!` | After the 1st Promo Concert |
| 11 | `Our Blue Bird Days` | After the 1st Promo Concert |
| 12 | `Run for Our Dream!` | After the 1st Promo Concert |
| 13 | `Grow Up and Shine!` | After the 2nd Promo Concert |
| 14 | `Hoppity Sunny Days♪` | After the 2nd Promo Concert |
| 15 | `Seven Colors Scenery` | After the 2nd Promo Concert |
| 16 | `Sunbeam Cheer` | After the 2nd Promo Concert |
| 17 | `Dream Sky` | After the 3rd Promo Concert |
| 18 | `Present March♪` | After the 3rd Promo Concert |
| 19 | `Precious Treasure Box` | After the 3rd Promo Concert |
| 20 | `The World's at Our Whim` | After the 3rd Promo Concert |
| 21 | `Sky-Blue Spring` | After the 3rd Promo Concert |
| 22 | `Fanfare for the Future!` | After the 3rd Promo Concert |
| 23 | `GIRLS' LEGEND U` | At the Grand Concert, in a normal and a special version |

**Re-derive the count from the harvested code:**

```bash
grep -ohE '"[A-Z][A-Za-z0-9 ,.!'"'"'☆+%-]{3,60}"' \
  .scratch-uma/phase0-scenario-research/raw/GrandLive*.kt | sort -u | wc -l
```

**Three title-level corrections this list makes against `docs/scenarios/07-grand-concert.md`:**

| The guide's reading | The client's |
|---|---|
| `Grow Up, Shine!` (GameTora's romaji column) | `Grow Up and Shine!` |
| `Hoppity Sunny Days ♪` (with a space before the note) | `Hoppity Sunny Days♪` |
| `RUN×RUN!` (GameTora's romaji column) | `Run n' Run!` |

Each is the same song under two renderings, and the client's is the one a surface may print.

## 6. Sources that were tried and produced nothing usable

Recorded rather than omitted, so a later pass does not spend the same fetch.

| Target | Result |
|---|---|
| `umamusume.com/api/ajax/pr_info_detail` with `{"announce_id":905}` | `{"response_code":500}` on every body shape tried. The notice index (`pr_info_index`) serves the same rows and was used instead |
| `umamusume.com/news/899` and `/news/905` as HTML | Returns the site shell with no notice body, exactly as `AGENTS.md` §18 records. Not evidence of an empty notice |
| A12, the official Grand Concert scenario page at its `umamusume.gg` path | Not reachable at fetch time. The three notices (A6–A8) carry the same title and date |
| `umamusu.wiki/Game:Our_Grand_Concert` | `page_not_found`. The real page title carries the exclamation marks: `Game:Brighter_Together!_Our_Grand_Concert` |
| `gametora.com/data/umamusume/ura-races.json` and `.test.json` | 404. The live path is `/data/umamusume/en/…` |
| JP-only rule pages (Game8 JP, GameWith, Kamigame) | Fetchable and used only where the Global page does not state the mechanic, per the brief's source priority. Every JP-sourced figure below is tagged JP |

---

## 7. How this register is used by the other five deliverables

- `ura-finale-test-case.md`, `trackblazer-test-case.md`, `our-grand-concert-test-case.md` cite these ids.
- `scenario-information-gaps.md` lists every mechanic where the highest tier available is `C`, and every figure
  two sources state differently.
- `scenario-test-data-matrix.md` cites the register only for the rows where research changed the app's expected
  behaviour; the rest of the matrix is measured against the app itself and says so.

**Source count: 14 tier-A rows, 7 tier-B rows, 8 tier-C rows — 29 in all — plus 5 captured Global frames and 3
Japanese frames.** A13 and A14 are not publisher pages and are labelled as such in their own rows: A13 is a
third party's captures, A14 a third party's string table. Re-derive with:

```bash
D=docs/audits/trainer-desk-v3-ux/scenario-research/scenario-research-sources.md
for t in A B C; do printf '%s: %s\n' "$t" "$(grep -cE "^\| $t[0-9]+ \|" $D)"; done
```

That command returns `A: 14`, `B: 7`, `C: 8` against the tree as it stands.
