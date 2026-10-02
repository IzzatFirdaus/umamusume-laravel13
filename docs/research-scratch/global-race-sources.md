# Global/EN source verification: race grades, race availability, turn calendar, 2026-07-01 rework

Scope: Global/English-language sources only. JP sources not used. Scratch only — nothing outside `research-scratch/` was touched.
Anchor date of all fetches: 2026-09-28. Raw HTML/JS copies live in `research-scratch/data/html/` (gitignored).

Source tiers used below: **S** = shipped-client data (GameTora `en/*` dumps), **A** = Global guide/wiki page, **B** = Global tool data.

---

## Q1. GRADE LABELS — RESOLVED. Code map 100/200/300/400/700 = G1/G2/G3/OP/Pre-OP.

### 1.1 The decisive source: uma.guide Global race data (server-side-served tool data)

- URL: `https://uma.guide/assets/chunks/uma-data.41Q6sBmn.js` (data chunk served by the Global EN site uma.guide; it is the dataset behind `https://uma.guide/agenda-planner/`, "Plan your career races across all three years, track Trackblazer epithet progress"). Site page date shown on sibling guide pages: "Last Updated: Apr 29, 2026" (`https://uma.guide/guides/trackblazer`). The chunk itself carries no date string.
- It stores both the numeric grade and an English label per race. Verbatim records, exact values:

```
{"raceId":4501,"raceName":"Aster Sho","grade":700,"gradeName":"Pre-OP","distance":1600,"distanceCategory":"Mile","ground":1,"groundName":"Turf","turn":1,"turnName":"Right","trackId":10005,"trackName":"Nakayama","entryNum":16,"schedules":[{"instanceId":450101,"month":9,"day":7,"half":1,"halfName":"First Half","time":2,"timeName":"Morning"}]}
{"raceId":4104,"raceName":"Andromeda Stakes","grade":400,"gradeName":"OP/L (Open/Listed)","distance":2000,"distanceCategory":"Middle","ground":1,"groundName":"Turf","turn":1,"turnName":"Right","trackId":10008,"trackName":"Kyoto","entryNum":18,"schedules":[{"instanceId":410401,"month":11,"day":18,"half":2,"halfName":"Second Half",...}]}
{"raceId":2032,"raceName":"Daily Hai Junior Stakes","grade":200,"gradeName":"G2","distance":1600,...,"trackName":"Kyoto",...}
{"raceId":3059,"raceName":"Artemis Stakes","grade":300,"gradeName":"G3","distance":1600,...,"trackName":"Tokyo",...}
{"raceId":1022,"raceName":"Asahi Hai Futurity Stakes","grade":100,"gradeName":"G1","distance":1600,...,"trackName":"Hanshin",...}
```

- Full code→label map read off all 1,251 parsed race records (count = records at that code):

| export `grade` | uma.guide `gradeName` | records | our export rows |
|---|---|---|---|
| 100 | `G1` | 704 | 55 |
| 200 | `G2` | 51 | 46 |
| 300 | `G3` | 76 | 76 |
| 400 | `OP/L (Open/Listed)` | 119 | 119 |
| 700 | `Pre-OP` | 26 | 26 |
| 800 | `Maiden (New Horse/Unraced)` | 53 | — |
| 900 | `Debut` | 38 | — |
| 999 | `URA Finals` | 8 | — |
| 1000 | `Aoharu Cup` | 160 | — |
| 0 | `Unknown (0)` | 16 | — |

> ⚠️ Lore flag for whoever promotes this table: the code-800 label above is quoted **verbatim** from the source (`Maiden (New Horse/Unraced)`, uma.guide's `gradeName` string). Per `CONSTRAINTS.md` C-4 the verbatim-source-data exemption applies here (a scratch quotation, not copy), but that equine-vocabulary fragment must never reach a display string, enum case, identifier or doc prose. If the 800 tier is needed in the app, name it from the in-game English term ("Unraced"/"Maiden") and have the Lore Guardian rule on it.

(The 100/200/300 record counts exceed export row counts because the chunk also carries Legend-Race and Room-Match variants of graded races. Codes 300 = 76 and 400 = 119 and 700 = 26 match our export exactly, row for row.)

- Requested named assignments, from this one source alone:
  - Daily Hai Junior Stakes (export 200) → **G2**
  - Artemis Stakes (export 300) → **G3**
  - Asahi Hai Futurity Stakes (export 100) → **G1**
  - Andromeda Stakes (export 400) → **OP / Open (Listed)**
  - Aster Sho, Akamatsu Sho, Saffron Sho, Rindo Sho, Shigiku Sho, Platanus Sho, Nadeshiko Sho (export 700) → **Pre-OP**
  - Soyokaze Sho → **NOT FOUND**. It is not in `races.json` (0 rows for `oyokaze`), not in the uma.guide chunk (0 hits for "Soyokaze"), and not in any Global page fetched here. Treat the name as wrong, not as an unmapped race.

### 1.2 Independent confirmation of the label set and its order (Game8, Global EN)

- URL: `https://game8.co/games/Umamusume-Pretty-Derby/archives/536131` — "List of All Races (G1, G2, G3, EX)". Page date: **"Last updated on: September 9, 2026 03:35 AM"**.
- Glossary row, verbatim: `Grade | Refers to the presence of higher-stat Umamusume. The higher the grade, the more difficult the race. | EX G1 G2 G3 OP Pre-OP Debut`
- The same page's tier icons carry these exact `alt` strings: `Debut Race.`, `EX Race.`, `G1 Race.`, `G2 Race.`, `G3 Race.`, `OP Race.`, `Pre-Op Race.` — i.e. the in-game grade icon set, named in English, in descending difficulty order.
- Same page's per-race table (`Period | Tier | Race | Distance`, 161 data rows) verbatim rows:
  - `Early Nov Junior | G2 | Daily Hai Junior Stakes Racecourse : Kyoto Turf 1600m Direction : Right Outer | Mile`
  - `Late Oct Junior | G3 | Artemis Stakes Racecourse : Tokyo Turf 1600m Direction : Left | Mile`
  - `Early Dec Junior | G1 | Asahi Hai Futurity Stakes Racecourse : Hanshin Turf 1600m Direction : Right Outer | Mile`
  - Tier values present in that table: only `G1` (43 rows), `G2` (42), `G3` (76). No OP/Pre-OP rows — the page covers graded races only, so it cannot name a Pre-OP race.
- Naming note for the export/guide pair: the export's `name_en` "Daily Hai Junior Stakes" is used verbatim by this Global guide. The alternate rendering "Daily Hai Nisai Stakes" was **NOT FOUND** in any Global source fetched here (Game8 and uma.guide both use "Junior").

### 1.3 Third confirmation of the ladder with numbers attached (Umamusume Wiki, Global-facing EN)

- URL: `https://umamusu.wiki/Game:Trackblazer` — page date: **"This page was last edited on 19 September 2026"** (oldid 76962).
- Verbatim, Trackblazer Participation/Result Points table: `Race Grade Result Points G1 100 G2 80 G3 60 OP 40 Pre-OP 20`
- Verbatim, bronze epithet: `Pro Racer | Win 10 OP level or higher races | 2 random stats +5` — establishes OP as a level with G3/G2/G1 above it, Pre-OP below it.
- Also on that page, the stat-ceiling row `1200 1900 1200 1200 1500` and `EN March 12, 2026` release date (matches our §1.3.4 Trackblazer row).

### 1.4 Fourth confirmation of the OP / Pre-OP vocabulary (Game8)

- URL: `https://game8.co/games/Umamusume-Pretty-Derby/archives/572831` — "Race Bonus Explained". Page date: **"Last updated on: June 26, 2026 04:24 AM"**.
- Tier icon `alt` strings on that page: `G1 Race`, `G2 Race`, `G3 Race`, `OP Race`, `Pre-Op Race`. The bonus table is keyed by Race Bonus % (10 / 20 / 34 "(Breakpoint for URA Finale and Unity Cup)" / 50 "(Breakpoint for Trackblazer)" / 60), and each threshold groups the tiers in descending order: one row for `G1 Race`, one row pairing `G2 Race` + `G3 Race`, one row pairing `OP Race` + `Pre-Op Race`. At the 34% breakpoint the three rows read, in order, `+4 to all stats +60 skill points`, `+4 to all stats +46 skill points`, `+4 to all stats +40 skill points` — so the OP/Pre-Op pair sits strictly below the G2/G3 pair, which independently confirms the ladder ordering G1 > G2/G3 > OP/Pre-Op.
- ⚠️ Marking quirk in this page worth knowing before quoting it: at 10% the low row's icons are `OP Race` + `Pre-Op Race`, but at 20/34/50/60% the same row's `alt` pair is `G2 Race` + `Pre-Op Race` — Game8 reuses the G2 image/alt in the OP slot. The tier *names* are still all present on the page; the row alignment is not trustworthy, so use this page for vocabulary and ordering, not for per-tier values.

### 1.5 Corroboration of the G2/G3 census from shipped Global client data (tier S)

- URL: `https://gametora.com/data/umamusume/missions/playertitle.<hash>.json` requested as manifest key **`en/missions/playertitle`** (Global client strings).
- Exact values in that dump: `Get a total of 76 G3 trophies` (id 600401), `Get a total of 42 G2 trophies` (600402), `Get a total of 34 G1 trophies` (600403).
- Game8's Global race table counts 76 G3 rows and 42 G2 rows — an exact match on both. Our export's unique-name counts: grade 300 = 76, grade 200 = 45. The three grade-200 names absent from the Global census are `Prix Niel`, `Prix Foy`, `Hanshin Umamusume Stakes (Story Race Use)` — all three are the export's three grade-200 rows flagged `unreleased_servers: ['en']`. That is an independent, Global-side confirmation of the `unreleased_servers` flag and of 200 = G2.

**Q1 verdict:** codes 200, 300 and 700 are no longer unverified. 200 = G2, 300 = G3, 400 = OP (Open/Listed), 700 = Pre-OP, and additionally 800 = Maiden, 900 = Debut, 999 = URA Finals, 1000 = Aoharu Cup. The `docs/UMAMUSUME_REFERENCE.md` §1.2.6 "❌ UNVERIFIED" sentence for 200/300/700 is now superseded.

---

## Q2. GLOBAL RACE AVAILABILITY — all 16 named dirt races ARE on Global, in the standard career calendar (not Trackblazer-only). Prix Niel and Prix Foy are NOT.

### 2.1 The Global announcement of the race additions (Game8, tier A)

- URL: `https://game8.co/games/Umamusume-Pretty-Derby/archives/607096` — "List of New Dirt Races". Page date: **"Last updated on: July 25, 2026 04:47 AM"**.
- Verbatim: `17 New Graded Dirt Races Added` … `A total of 17 new graded Dirt races have been added to the global version of Umamusume: Pretty Derby. All of these new races are featured in the Morioka, Funabashi, Oi, and Kawasaki racecourses. In addition new versions of the JBC Ladies' Classic, JBC Sprint, and JBC Classic are also available on all the new racecourses.`
- Its per-race entries give venue, year band, season, surface, distance, direction and the entry gate. Exact values (verbatim, "Fans Required"):

| race (Game8 spelling) | Game8 Global entry gate | export `fans_needed` | agree? |
|---|---|---|---|
| Mile Championship Nambu Hai | 12,000 | 12,000 (as `M.C. Nambu Hai`, id 111001) | yes (alias) |
| Mercury Cup | 1,000 | 1,000 | yes |
| Cluster Cup | 1,000 | 1,000 | yes |
| Kashiwa Kinen | 12,000 | 12,000 | yes |
| Sazanka TV Hai | 1,800 | 1,800 | yes |
| Diolite Kinen | 1,800 | 1,800 | yes |
| Marine Cup | 1,000 | 1,000 | yes |
| Queen Sho | 1,000 | 1,000 | yes |
| Tokyo Hai | 1,800 | 1,800 | yes |
| Ladies Prelude | 1,800 | 1,800 (export `Ladies' Prelude`) | yes |
| TCK Jo-o Hai | 1,000 | 1,000 | yes |
| Tokyo Sprint | 1,000 | 1,000 | yes |
| Zen-Nippon Junior Yushun | 1,000 | 1,000 | yes |
| Kawasaki Kinen | 12,000 | 12,000 | yes |
| Kanto Oaks | 1,800 | 1,800 | yes |
| Empress Hai | 1,800 | 1,800 | yes |
| Sparking Lady Cup | 1,000 | 1,000 | yes |

17/17 gate values match the export once the `M.C. Nambu Hai` / `Mile Championship Nambu Hai` alias is accounted for.

### 2.2 They are career-wide, not Trackblazer-only

Three independent points:

1. **Global client strings (tier S).** Manifest key `en/missions/playertitle` (Global career title missions) contains, verbatim: `Win 200 races at the Ooi racetrack` (id 600511), `Win 200 races at the Kawasaki racetrack` (600512), `Win 200 races at the Funabashi racetrack` (600513), `Win 200 races at the Morioka racetrack` (600514). These are cross-career titles, not scenario-scoped, and they exist in the Global build. The same dump has `Win the Twinkle Star Climax once in 'Make a new track'` (600414), i.e. when a mission IS scenario-bound the Global text says so — the racetrack missions do not.
   - Game8's version of the same four titles, verbatim: `Win 200 Races at the Oi Racecourse in Career`, `... the Kawasaki Racecourse in Career`, `... the Funabashi Racecourse in Career`, `... the Morioka Racecourse in Career`.
2. **The Global all-races calendar (tier A).** `https://game8.co/games/Umamusume-Pretty-Derby/archives/536131` (Sept 9, 2026) lists them in its main searchable table with tiers and Classic/Senior/Junior periods, e.g. verbatim `Late Dec Junior | G1 | Zen-Nippon Junior Yushun Racecourse : Kawasaki Dirt 1600m Direction : Left | Mile`, `Early Feb Senior | G1 | Kawasaki Kinen ...`, `Early May Senior | G1 | Kashiwa Kinen ...`, `Late Jan Senior | G3 | TCK Jo-o Hai ...`. That page is the general race reference, not a scenario page.
3. **The export's URA schedule (tier B, corroborating).** All 16 named races carry rows in `ura-races.json` (the URA Finale schedule): e.g. `M.C. Nambu Hai` at (year 2, month 10, half 1) and (year 3, month 10, half 1), `fans_needed` 12000. So the slots exist in the URA Finale calendar, which means they are not Trackblazer additions. `Prix Niel` and `Prix Foy` also carry URA rows — but flagged `unreleased_servers: ['en']`.
   - Caveat worth keeping: `ura-races.json` is an unprefixed (server-shared) manifest key, so on its own it does not prove Global presence; it only proves the slot is a URA-Finale slot rather than a Trackblazer-only slot. Points 1 and 2 are what carry the Global claim.

Trackblazer-specific text in the same Game8 dirt page is about **epithets**, not about race availability, verbatim: `Win three (3) graded stakes races held in Fukushima, Niigata, or Morioka. (Limited to the Trackblazer scenario)` and `Morioka has been added as a requirement for obtaining the Tohoku Top Dog Epithet, and the Epithet is now be limited to the Trackblazer scenario.` (sic, the page's own typo). So: the *reward for seeking those venues out* is Trackblazer-bound; the races themselves are career-wide. This matches our §1.6 and game8's career-guide line `Trainers are free to participate in any race without any schedule restriction, making it easier to collect trophies` (`.../archives/536350`, July 24, 2026).

### 2.3 Prix Niel and Prix Foy: NOT FOUND on Global

- Game8 `.../archives/536131` (Sept 9, 2026): `Prix` = 0 occurrences, `Niel` = 0, `Foy` = 0. That page also contains 0 occurrences of `Prix de l'Arc de Triomphe`, so the same absence test correctly flags two other known `[JP]`-only items.
- uma.guide data chunk (`uma-data.41Q6sBmn.js`): `Prix Niel` = 0, `Niel` = 0, `Prix Foy` = 0, `Foy` = 0. Its career schedule block contains no race name matching `Prix`.
- Combined with `unreleased_servers: ['en']` in the export, this is a three-way agreement: **these two G2 races are not in the Global career calendar**.
- Same absence applies to `American Oaks` and `Queen Elizabeth Cup` (export grade-100 names flagged `unreleased_servers: ['en']`), which are also not in the uma.guide Global schedule.
- Positive Global-side statement for why: `https://umamusu.wiki/Game:Career_Mode` (8 September 2026) lists the scenarios on Global as URA Finale, Unity Cup, Trackblazer, Grand Concert, and marks the rest with an asterisk explained verbatim as `(*) Scenario not available on English Vesion` (sic). Among the asterisked ones is `Project L'Arc *` — `Work with your trainee to achieve the dream of every Japanese Umamusume: to become victorious in l'Arc!` — which is the scenario owning the Prix de l'Arc de Triomphe and its French prep races. So `Prix Niel` / `Prix Foy` / `Prix de l'Arc de Triomphe` absence on Global is scenario-driven, not an oversight.

### 2.4 The `fans_gain` 51-56 premise is real but narrower than stated, and does not indicate missing races

From `ura-races.json` joined to `en/race-fans` (manifest key `en/race-fans`, ids 1-50) — **exactly 7 rows** in the whole URA schedule reference a payout curve Global does not ship:

```
gain=51 Diolite Kinen              grade=200  unrel=None      dne=pre_nar        fans_needed=1800
gain=51 Sazanka TV Hai             grade=200  unrel=None      dne=pre_nar        fans_needed=1800
gain=52 Tokyo Sprint               grade=300  unrel=None      dne=pre_nar        fans_needed=1000
gain=53 Queen Sho                  grade=300  unrel=None      dne=pre_nar        fans_needed=1000
gain=54 Zen-Nippon Junior Yushun   grade=100  unrel=None      dne=pre_nar        fans_needed=1000
gain=56 Prix Foy                   grade=200  unrel=['en']    dne=pre_2_5th_anni fans_needed=2000
gain=56 Prix Niel                  grade=200  unrel=['en']    dne=pre_2_5th_anni fans_needed=2000
```

- So the five dirt races that hit ids 51-54 are **not** the same set as the eleven `pre_nar` races: `Kawasaki Kinen` (41), `Kashiwa Kinen` (43), `Cluster Cup` (10), `Mercury Cup` (10), `Marine Cup` (49), `Sparking Lady Cup` (49), `TCK Jo-o Hai` (9), `Kanto Oaks` (15), `Empress Hai` (15), `Tokyo Hai` (15), `Ladies' Prelude` (13) all reference curve ids Global already ships.
- Per-server curve counts in the same manifest family: `en/race-fans` = 50 curves (ids 1-50); `ko/race-fans` = 57; `zh_tw/race-fans` = 57; `race-fans` (JP) = 61. So Global is the shortest curve table, and ids 51-54 exist on KO/TW. The most defensible reading is that GameTora's `en/race-fans` dump lags the Global build for the July-2026 additions (or the new races reuse a curve delivered elsewhere), **not** that the five races are absent — because 2.1, 2.2 and the 17/17 fan-gate agreement say they are present. Flag as an unresolved data-side gap; do not use it as availability evidence.

**Q2 verdict:** present on Global and in the ordinary career calendar (all scenarios, URA slots included): Kawasaki Kinen, Kashiwa Kinen, Zen-Nippon Junior Yushun, Queen Sho, Diolite Kinen, Sazanka TV Hai, Tokyo Sprint, Cluster Cup, Marine Cup, Mercury Cup, Sparking Lady Cup, TCK Jo-o Hai, Kanto Oaks, Empress Hai, Tokyo Hai, Ladies' Prelude. Not present on Global: Prix Niel, Prix Foy (and, same flag, American Oaks / Queen Elizabeth Cup / the story-race-only Hanshin Umamusume Stakes).

---

## Q3. TURN CALENDAR SHAPE — CONFIRMED by three Global sources. No source anywhere says the year begins in March.

### 3.1 24 turns per year, months January-December, two turns per month

- URL: `https://umamusu.wiki/Game:Career_Mode` — page date: **"This page was last edited on 8 September 2026, at 20:39"** (oldid 76056).
- Verbatim: `Most scenarios take place over the course of a three-year schedule where each month is divided into two turns; the player is given a total of 72 turns, excluding the 6-turn finale period that takes place after the third year.`
- Verbatim (on-screen date format): `format of "[Descriptor] Year Early/Late [Month]"`.
- Independent structural confirmation, uma.guide Global schedule data (`https://uma.guide/assets/chunks/uma-data.41Q6sBmn.js`, agenda-planner dataset): the career-race block has **exactly 24 distinct `turn` keys per year — `01_01, 01_02, 02_01 … 12_01, 12_02`** — i.e. month 01-12 × half 1-2, and the race DB's own schedule objects spell the halves `"halfName":"First Half"` / `"Second Half"`. Race-year labels in the same block are `"First Year"`, `"Second Year"`, `"Third Year"` (30 / 161 / 185 race entries).
- Finale-period confirmation (Global wiki, tier A): `https://umamusu.wiki/Game:URA_Finale`, **"last edited on 7 September 2026"** — verbatim `After three years of training, 6 extra turns are provided for the URA Finale.`

### 3.2 Mandatory Junior Make Debut at turn 12 (Late June of Junior year)

- URL: `https://game8.co/games/Umamusume-Pretty-Derby/archives/536520` — "URA Finale Scenario Guide", "List of Fixed Events", Junior Year table. Page date: **"Last updated on: July 6, 2026 06:18 AM"**.
- Verbatim table rows: `Road to Stardom | After 3 Turns, Pre-Debut | • Director Akikawa will now appear in training.` then `[Race] Junior Make Debut | After 11 turns | • Debut Race` then `After the Debut | After the Debut Race | • Promotion to Beginner Class/Can run other races.` then `A Quirky Respondent? | Early July(Last turn) | • Reporter Etsuko Otonashi will now appear in training.`
- That pins it: debut fires after 11 turns ⇒ **turn 12**, and the next scripted Junior event sits on the last turn of Early July ⇒ turn 13 = Early July ⇒ turn 12 = Late June ⇒ turn 1 = Early January.
- The export's own EN description agrees, verbatim (manifest key `ura-races`, ids `debut`): `The Debut Race is mandatory for every character, and takes place in the second half of June of the first year.` Export slot: `year 1, month 6, half 2`.
- Year-end/late-month corroboration, `https://game8.co/games/Umamusume-Pretty-Derby/archives/545572` ("Unity Cup / Aoharu Hai" guide, **"Last updated on: July 7, 2026 02:39 AM"**): verbatim `Unity Cup Team Race Schedules Round 1 After Junior Year Late Dec | Round 2 After Classic Year Late June | Round 3 After Classic Year Late Dec | Round 4 After Senior Year Late June | Finals After Senior Year Late Dec`.
- Wiki fan-goal rows on the same Global URA page also use the Late-December year boundary: verbatim `At the Carrot Farm | Have 100,000 fans by Late December of Classic Year` and `Going to the URA Finale! | Have 240,000 fans by Late December of Senior Year`.

### 3.3 March-start check: explicitly negative

- **NOT FOUND — no Global source read here says or implies the training year begins in March.** Checked: Game8 536131 / 536520 / 545572 / 572831 / 580723 / 607096 / 611986 / 536350, uma.guide (trackblazer guide, agenda planner + data chunk), umamusu.wiki (Career Mode, URA Finale, Trackblazer, Conditions, New Player Guide), web searches for a March-start claim (only unrelated non-game results).
- One trap worth naming so nobody re-derives it wrong: Game8's Junior-year race table (`.../archives/536131`, "Junior Year Races") begins at `Late Jul Junior`, and the uma.guide First-Year schedule's earliest turn key is `07_02`. That is the first **race** of the Junior year, not the first **turn**. Turn 1 of the Junior year is Early January (see 3.2, and `New Year's Resolutions | Early January(Start of Turn)` in the Classic-year table of 536520).

---

## Q4. 2026-07-01 GLOBAL REWORK — it did NOT touch the career race calendar. The calendar change was 2026-07-22.

### 4.1 What the rework did change, as documented on Global

- URL: `https://game8.co/games/Umamusume-Pretty-Derby/archives/538351` — "July 2026 Balance Adjustments and Patch Notes". Page date: **"Last updated on: July 23, 2026 10:25 PM"**.
- Its complete section list is: `URA and Unity Cup Scenario Updates`, `Stat Cap Increases`, `Spark Rerolls Implemented`, `Fully Charged Mechanic Added`, `Front Runner Adjustments`, `Position Adjustments`, `Debuff Skill Activations`, `General Balance and Text Adjustments`, `Skill Adjustments`.
- Verbatim: `The URA Finale and Unity Cup Scenarios have been updated. They introduce new features such as training with Happy Meek and Extreme Spirit Bursts.`
- Verbatim caps: `Trackblazer 1200 1900 1200 1200 1500 / Unity Cup 1300 1300 1300 1300 1800 / URA Finale 1400 1400 1400 1400 1400` with `Attribute caps, or stat caps, have also been increased with the July 1 update. Note that gains past 1200 are halved and the effect past 1200 is also reduced by around half.`
- Verbatim: `Spark rerolls are now available as of July 1. This will let you spend 30 TP once to try for another set of sparks.` / `A new mechanic called Fully Charged has been added to the game. This activates when a runner has over 1200 Power.`
- Verbatim, the Career-mode catch-all: `Several adjustments have been made to Career Mode as well as race mechanics. One notable change is that stats beyond 1200 should now be 1/2 as effective rather than 1/8 as it originally was in the Japanese version prior to Grand Live. Other changes include Career Event rewards, affinity bonuses, post-Outing events, the Pure Passion condition, and various race and skill mechanics.`

### 4.2 The race calendar was NOT part of it — stated positively

- Same page, verbatim: `Note that the new Dirt racetracks and their corresponding skills don't appear to be available yet.` — i.e. as of the July 1 balance patch, the four new dirt racecourses were still not live on Global. This is the direct ruling-out.
- Whole-page keyword scan of 538351 (visible text, case-sensitive counts): `fans` = 0, `fan ` = 0, `entry` = 0, `distance` = 0, `finals`/`Finals` = 0, `schedule` = 0, `calendar` = 0, `Make Debut` = 0, `Pre-OP` = 0, `Kawasaki` = 1 (nav link only), `Morioka` = 1 (nav link only). No race-gate, distance, availability or finals change is listed.
- Timeline from Global-side pages, verbatim:
  - `https://game8.co/games/Umamusume-Pretty-Derby/archives/607273` ("July 2026 Release Schedule", **"Last updated on: July 26, 2026 10:22 PM"**) lists: `Balance Adjustments and Bond Level Uncaps (July 1)` … `1.5th Anniversary Celebration (July 14)` … `New Scenario: Grand Live (July 22)` … `Wings of Steam and Steel Story Event (July 27)`.
  - `https://game8.co/games/Umamusume-Pretty-Derby/archives/536350` ("Career Scenario Mode Guide", **"Last updated on: July 24, 2026 10:16 AM"**): `Brighter Together: Our Grand Concert (also known as Grand Live) is the fourth permanent scenario, released on July 22, 2026.`
  - `https://game8.co/games/Umamusume-Pretty-Derby/archives/607096` ("List of New Dirt Races") is dated **July 25, 2026**, i.e. it documents the post-July-22 state.
  - `https://game8.co/games/Umamusume-Pretty-Derby/archives/611986` ("July 22, 2026 Server Maintenance", **"Last updated on: July 22, 2026 04:00 AM"**) is schedule-only (`The maintenance period lasted 4:00 AM to 8:00 AM (UTC)`); it carries no changelog.
  - `https://game8.co/games/Umamusume-Pretty-Derby/archives/607082` ("July 1, 2026 Server Maintenance", **"Last updated on: June 30, 2026 10:15 PM"**) is likewise schedule-only: `A server maintenance session ... was scheduled on July 1, 2026 UTC. The maintenance period lasted 2:00 AM to 11:00 AM (UTC) on the same day.`
- Wiki confirmation that July 1 was a *scenario* update, and that the finals structure is unchanged since: `https://umamusu.wiki/Game:URA_Finale` (7 September 2026) infobox verbatim `EN June 26, 2025 EN Update July 1, 2026`, body verbatim `It was the 1st scenario introduced with the game's launch, before being updated on December 12, 2022 (July 1, 2026 in Global Version) to expand on existing features and adding new mechanics.` The same page still describes the finals as `There are 3 races of the URA Finale. The Qualifier, the Semifinal, and the Finals. Before each race, 1 turn of training is provided. Each race must be won to advance to the next race or the scenario will end with a normal ending for the trainee.`
- Fan gates after the whole July sequence: the 17 Global fan-gate values in 2.1 (1,000 / 1,800 / 12,000) still match the export dumped 2026-09-27, so no later change moved them either.

**Q4 verdict — rule out a calendar change on 2026-07-01.** The rework changed stat caps, spark rerolls, Fully Charged, running-strategy and debuff activation behaviour, and added Extreme Spirit Bursts to URA/Unity Cup. It changed nothing in the career race calendar: no entry-gate, availability, distance or finals change is documented by the Global patch guide, which explicitly notes the new dirt racetracks were still unavailable at that point. **But the calendar did change on 2026-07-22** (17 new graded dirt races at Morioka / Funabashi / Oi / Kawasaki + new Winner's Sashes + Smart Falcon/Agnes Digital event additions + G1/G2/G3 title requirements rewritten from fixed counts to "all"). A doc that dates the calendar change to July 1 would be wrong by three weeks.

---

## Tooling notes / negative results (so gaps aren't read as absence)

- `https://umamusume.com/news/1050` — **JS shell confirmed**, not an absence: plain GET returns `302` → `200` at `https://umamusume.com/news/1050/`, 4,308 bytes, and after stripping markup the entire visible text is 50 characters: `Umamusume: Pretty Derby Official Website | Cygames`. The official Global changelog itself could therefore not be read with a plain fetch; Q4 rests on the Game8 patch guide (A) and the Global wiki (A) instead.
- `https://umamusu.wiki/api.php` → `404 Not Found` (API disabled), so wiki search-by-keyword was not possible; page-set navigation from `Game:Career_Mode` links was used instead.
- `https://www.reddit.com/r/UmaMusume/comments/1lwuwfz.json` (a "Race Tiers Explanation" thread that surfaced in search) → `403`. Not used; it is community-tier anyway and the tier-A/B sources above already settle Q1.
- GameTora HTML pages were not fetched at all (per instructions they return an empty shell); only `https://gametora.com/data/umamusume/<key>.<hash>.json` endpoints with browser User-Agent + `Accept: application/json` were used.
- There is **no `en/races` and no `en/ura-races` key in the manifest** (278 keys; EN-prefixed keys cover only fans, rewards, missions, events, gacha, foresight, story events, mobs, layout data). The race master table is server-shared, so `unreleased_servers` on `races` is the only Global-exclusion mechanism GameTora publishes — which is why the Q2 answer leans on Game8 + uma.guide + `en/missions/*` rather than on an EN race table.
- Game8's own "Junior Year Races" table (`.../archives/536131`) omits `Zen-Nippon Junior Yushun` (Late Dec Junior) while its searchable all-races table includes it; treat the per-year sub-tables as the less reliable of the two.

## Open items this pass did NOT close

1. `en/race-fans` ships 50 curves while KO/TW ship 57 and JP 61, and 5 URA rows on Global-side races point at ids 51-54 (§2.4). Global payout curves for the newest dirt races are therefore still unrecovered from any source; a UI must not claim a fan-gain curve for those five races.
2. `Soyokaze Sho` does not exist in the Global race data or in our export (Q1) — whoever named it should be asked where it came from.
3. Grade code `0` appears 16 times in the uma.guide dump as `Unknown (0)`; our export does not carry code 0 at all, so nothing to map there, but the label is unaudited.
