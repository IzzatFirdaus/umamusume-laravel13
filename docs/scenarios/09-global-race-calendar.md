# Global Career Race Calendar (Junior–Senior Year)

**Server:** `[Global]`
**Status:** Active — source of truth for career race timing, tier, and entry gates
**Last Verified:** 2026-09-28 (export re-resolved against the live manifest; zero hash drift against the 2026-09-27 snapshot)
**Superseded By:** nothing for timing data. Team Race timing lives in `06-unity-cup-gametora.md`; Trackblazer Grade Point deadlines live in `04`/`05`. Those tables are deliberately **not** re-pasted here — `02-unity-cup.md` records what duplication did to this file set once already.

---

## Read this before using the tables

This file answers **"which race exists at which turn, and what does it cost to enter"**. It is the availability calendar that `scenario_slots` needs and that `docs/scenarios/01-ura-finale.md` never had (`docs/adr/0003` Amendment R3).

Two premises in the commissioning brief do not survive contact with the data and the client, and they are corrected here rather than carried forward:

1. **There is no single fixed list of mandatory races.** Only two races are mandatory for every trainee in every run: the Junior Make Debut and the scenario's own final. Everything else the client marks **Goal** is per-character. Four `[Global]` client panels in the capture corpus show four different Goal sets — one trainee's Senior goals are Tenno Sho (Autumn) and Arima Kinen, another's are Nikkei Sho, Tenno Sho (Spring), Takarazuka Kinen and Arima Kinen; in Classic one has NHK Mile Cup, Mile Championship and Arima Kinen while another has Spring Stakes, Tokyo Yushun and Kikuka Sho. See "Client corroboration" below. A `scenario_slots` row therefore cannot carry a universal `is_mandatory` flag derived from this calendar alone; the per-character goal table is a separate, unsourced object.
2. **The brief's worked example is half wrong.** `Hopeful Stakes | G1 | 2000m | 1,000 fans | Late Dec, Junior Year` reproduces the export exactly. `Junior Make Debut | Debut | 1600m | 0 fans` does not: the debut has **no fixed distance or surface** — the export stores `99999` sentinels and the client text says the debut "var[ies] in length and surface" by character. Any importer that hardcodes 1600 m for the debut is inventing a number.

**Do not project fans through nine slots.** `[Global]` has no first-place payout curve for them, so their "Fans for 1st" cell reads ⚠️ rather than a number. They are named here because a reader who copies one row out of a table below will not otherwise meet the caveat until Known gaps:

| Year | Turn | Slot | Race | Missing curve |
|---|---|---|---|---|
| Junior | 24 | Late December | Zen-Nippon Junior Yushun | 54 |
| Classic | 17 | Early September | Prix Niel | 56 |
| Classic | 18 | Late September | Sazanka TV Hai | 51 |
| Classic | 23 | Early December | Queen Sho | 53 |
| Senior | 6 | Late March | Diolite Kinen | 51 |
| Senior | 8 | Late April | Tokyo Sprint | 52 |
| Senior | 17 | Early September | Prix Foy | 56 |
| Senior | 18 | Late September | Sazanka TV Hai | 51 |
| Senior | 23 | Early December | Queen Sho | 53 |

Seven are the regional-racing slots added by the `[JP]` `pre_nar` update; the two Longchamp slots are the ones excluded from `[Global]` outright, so they carry a second reason to be ignored. The gap is explained in `[Global]` versus `[JP]` finding 3, and the ⚠️ markers in the year tables stay.

## How a career year is laid out

`[Global]` runs three career years of **24 turns each, two per month, January through December**. The turn number in every table below is `(month − 1) × 2 + half`, so Early January is turn 1 and Late December is turn 24. The client panel labels its cells `Early Jan … Late Dec` in exactly that order, which is what pins the mapping.

The Junior Year grid is not full. **Turns 1–11 (Early January through Early June) have no race slots at all** — a trainee cannot race before debuting. The mandatory debut lands at **turn 12 (Late June)**, which is the same fact `01-ura-finale.md` states as "After 11 turns"; the two phrasings agree only under a January start, so the calendar and the guide corroborate each other's year shape.

## Tier labels, and how each one was pinned

`docs/UMAMUSUME_REFERENCE.md` §1.2.6 pinned `100 = G1` and `400 = OP` and left codes 200, 300 and 700 as `❌ UNVERIFIED`. That gap closes here.

The export stores five grade codes for ordinary races plus three for special ones. `[Global]`'s published tier set has exactly five labels — Pre-OP, OP, G3, G2, G1 — so once four codes are pinned the fifth follows by elimination.

| Code | Tier | Evidence |
|---|---|---|
| 100 | G1 | Client skill copy "G1 Averseness … decrease performance in G1 or otherwise important races" (`skills.json` id 200311), as already recorded in §1.2.6. |
| 200 | G2 | **Pinned by distribution match.** See the table below: the count of unique G2 races per distance band matches uma.guide's published `6 / 12 / 13 / 5` cell for cell. |
| 300 | G3 | Same test: `18 / 33 / 17 / 1` matches exactly. |
| 400 | OP | Three rows whose in-game names contain 「オープン」 carry grade 400 (§1.2.6). |
| 700 | Pre-OP | By elimination from the five-label set, and consistent on its own terms: all 26 slots are Junior Year only, and the names are `…Sho`/`…Special`, the non-stakes pattern. |
| 800 | Maiden | Client row name `Junior Maiden Race`, with the client rule quoted in the export: "You can't participate in any races listed here until you win either Debut or any of the Maiden Races". |
| 900 | Debut | Client row name `Junior Make Debut`, "mandatory for every character". |

The 12-cell test, run against the `[Global]`-filtered career pool with the 23 rows carrying a `did_not_exist` marker excluded:

| Band | G1 (code 100) | G2 (code 200) | G3 (code 300) |
|---|---|---|---|
| Sprint (≤ 1400 m) | 3 | 6 | 18 |
| Mile (1401–1800 m) | 10 | 12 | 33 |
| Medium (1801–2400 m) | 14 | 13 | 17 |
| Long (≥ 2401 m) | 3 | 5 | 1 |
| **uma.guide published** | **3 / 10 / 14 / 3** | **6 / 12 / 13 / 5** | **18 / 33 / 17 / 1** |

Twelve of twelve cells agree with [uma.guide's Trackblazer guide, page last updated 2026-04-29](https://uma.guide/guides/trackblazer), as transcribed in `docs/scenarios/04-trackblazer-umaguide.md`. A swapped 200/300 mapping would fail every row. Including the `did_not_exist` rows breaks the match in each band where they add races, so the guide's table describes the same race universe as the base pool — whether because its sub-table predates those additions or because it counts a narrower set is **not established**, and is listed under Known gaps.

Distance bands are the published ones: Sprint 1400 m and under, Mile 1401–1800, Medium 1801–2400, Long 2401 up.

## Naming: `[Global]` says "Junior", `[JP]` guides say "Nisai"

The export's `name_en` values are client strings and use **Junior** where Japanese guides and any JP-translated list use **2-year-old / Nisai**. `Daily Hai Junior Stakes`, `Kokura Junior Stakes`, `Kyoto Junior Stakes`, `Tokyo Sports Hai Junior Stakes`, `Zen-Nippon Junior Yushun` are the `[Global]` forms. A brief or guide that cites "Daily Hai Nisai S." or "Kokura Nisai S." is quoting a JP rendering; join on the client name, not the translation. `races_extended.json` carries the rename history — for one example, "This race later became the Hopeful Stakes".

### Lore note, stated so the Guardian need not re-litigate it

One race name in these tables contains a term from the wider dictionary: **Hanshin Juvenile Fillies** (Junior, Early December), against the singular-female row at `docs/design-research/CONSTRAINTS.md:58`. It is a **verbatim `[Global]` client race name kept as source data**, which is the third category in `CONSTRAINTS.md` C-4 — it stays exactly as the client prints it, and the gate goes on the display path, not on the data. Editing it to satisfy the style rule would break the join to `race_instances`. The same treatment already applies to Air Messiah and the `[Global]` "Cleat" case in `docs/UMAMUSUME_REFERENCE.md` §2.7.

**Gate result, observed rather than predicted.** With this file staged so `git grep` can reach it, the repo's lore sweep reports **140 hits, none of them from this file's tables**. The client name does not fire because every lore pattern is word-matched (`-inwE`) and the name is plural.

The trap that this note walked into is worth recording, because it is the one an explanatory file creates for itself: **an earlier revision of this very note quoted the banned singular token as a bare inline code span, and that is what raised the count to 141.** The race name passed; the ruling about the race name failed. A lore note must therefore describe the dictionary row by location, not by reproducing its contents — which is what the paragraph above now does. `docs/scenarios/08-grand-masters-jp-only.md` lines 40–42 are the existing precedent for the other treatment, quoting a banned term to warn against it and being carried as an explained hit.

## Mandatory and conditional races

These nine rows are not on the monthly grid. The four finals variants are the only place the `[Global]` scenarios differ in this dataset.

| Turn | Slot | Race | Tier | Distance | Surface | Track | Fans to enter | Fans for 1st | Notes |
|---|---|---|---|---|---|---|---|---|---|
| 12 | Late June | Junior Make Debut | Debut | - | flexes | - | - | - | distance/surface flex with your most-run types; mandatory |
| 13 | Early July | Junior Maiden Race | Maiden | - | flexes | - | - | - | distance/surface flex with your most-run types; conditional |
| - | after Senior Dec | Grand Masters | G1 | - | flexes | - | - | - | added by `pre_2nd_anni`; distance/surface flex with your most-run types; conditional |
| - | after Senior Dec | Twinkle Star Climax | G1 | - | flexes | - | - | - | added by `pre_mant`; distance/surface flex with your most-run types; conditional |
| - | after Senior Dec | URA Finals Final (Aoharu) | G1 | - | flexes | - | - | - | added by `pre_aoharu`; distance/surface flex with your most-run types; conditional |
| - | after Senior Dec | URA Finals Final (Grand Live) | G1 | - | flexes | - | - | - | added by `pre_gl`; distance/surface flex with your most-run types; conditional |
| - | after Senior Dec | URA Finals Final (URA) | G1 | - | flexes | - | - | - | distance/surface flex with your most-run types; conditional |
| - | after Senior Dec | URA Finals Qualifier | G1 | - | flexes | - | - | - | distance/surface flex with your most-run types; conditional |
| - | after Senior Dec | URA Finals Semifinal | G1 | - | flexes | - | - | - | distance/surface flex with your most-run types; conditional |

The three URA Finals rounds are shared: only the **Final** row forks per scenario, and which fork belongs to which scenario is a join on the export label, not on a name — `Grand Live` is the export label for `[Global]`'s Our Grand Concert, whose client title is "Brighter Together Our Grand Concert". `Grand Masters` has no `start_en` in `scenarios.json` and is `[JP-Only]`; per `docs/SOURCE-OF-TRUTH.md` §4.1 it must not be imported.

## Junior Year

| Turn | Slot | Race | Tier | Distance | Surface | Track | Fans to enter | Fans for 1st | Notes |
|---|---|---|---|---|---|---|---|---|---|
| 12 | Late June | Junior Make Debut | Debut | - | flexes | - | - | - | distance/surface flex with your most-run types; mandatory |
| 13 | Early July | Junior Maiden Race | Maiden | - | flexes | - | - | - | distance/surface flex with your most-run types; conditional |
| 14 | Late July | Chukyo Junior Stakes | OP | 1,600 m | Turf | Chukyo | 350 | 1,600 |  |
| 14 | Late July | Hakodate Junior Stakes | G3 | 1,200 m | Turf | Hakodate | 350 | 3,100 |  |
| 15 | Early August | Cosmos Sho | OP | 1,800 m | Turf | Sapporo | 350 | 1,600 |  |
| 15 | Early August | Dahlia Sho | OP | 1,400 m | Turf | Niigata | 350 | 1,600 |  |
| 15 | Early August | Phoenix Sho | OP | 1,200 m | Turf | Kokura | 350 | 1,600 |  |
| 16 | Late August | Clover Sho | OP | 1,500 m | Turf | Sapporo | 350 | 1,600 |  |
| 16 | Late August | Niigata Junior Stakes | G3 | 1,600 m | Turf | Niigata | 350 | 3,100 |  |
| 17 | Early September | Aster Sho | Pre-OP | 1,600 m | Turf | Nakayama | 350 | 1,000 |  |
| 17 | Early September | Nojigiku Stakes | OP | 1,800 m | Turf | Hanshin | 350 | 1,600 |  |
| 17 | Early September | Suzuran Sho | OP | 1,200 m | Turf | Sapporo | 350 | 1,600 |  |
| 17 | Early September | Kokura Junior Stakes | G3 | 1,200 m | Turf | Kokura | 350 | 3,100 |  |
| 17 | Early September | Sapporo Junior Stakes | G3 | 1,800 m | Turf | Sapporo | 350 | 3,100 |  |
| 18 | Late September | Saffron Sho | Pre-OP | 1,600 m | Turf | Nakayama | 350 | 1,000 |  |
| 18 | Late September | Canna Stakes | OP | 1,200 m | Turf | Nakayama | 350 | 1,600 |  |
| 18 | Late September | Fuyo Stakes | OP | 2,000 m | Turf | Nakayama | 350 | 1,600 |  |
| 18 | Late September | Kikyo Stakes | OP | 1,400 m | Turf | Hanshin | 350 | 1,600 |  |
| 19 | Early October | Platanus Sho | Pre-OP | 1,600 m | Dirt | Tokyo | 350 | 1,000 |  |
| 19 | Early October | Rindo Sho | Pre-OP | 1,400 m | Turf | Kyoto | 350 | 1,000 |  |
| 19 | Early October | Shigiku Sho | Pre-OP | 2,000 m | Turf | Kyoto | 350 | 1,000 |  |
| 19 | Early October | Momiji Stakes | OP | 1,400 m | Turf | Kyoto | 350 | 1,600 |  |
| 19 | Early October | Saudi Arabia Royal Cup | G3 | 1,600 m | Turf | Tokyo | 350 | 3,300 |  |
| 20 | Late October | Nadeshiko Sho | Pre-OP | 1,400 m | Dirt | Kyoto | 350 | 1,000 |  |
| 20 | Late October | Hagi Stakes | OP | 1,800 m | Turf | Kyoto | 350 | 1,700 |  |
| 20 | Late October | Ivy Stakes | OP | 1,800 m | Turf | Tokyo | 350 | 1,700 |  |
| 20 | Late October | Artemis Stakes | G3 | 1,600 m | Turf | Tokyo | 350 | 2,900 |  |
| 21 | Early November | Hyakunichiso Tokubetsu | Pre-OP | 2,000 m | Turf | Tokyo | 350 | 1,000 |  |
| 21 | Early November | Kigiku Sho | Pre-OP | 2,000 m | Turf | Kyoto | 350 | 1,000 |  |
| 21 | Early November | Kimmokusei Tokubetsu | Pre-OP | 1,800 m | Turf | Fukushima | 350 | 1,000 |  |
| 21 | Early November | Oxalis Sho | Pre-OP | 1,400 m | Dirt | Tokyo | 350 | 1,000 |  |
| 21 | Early November | Fukushima Junior Stakes | OP | 1,200 m | Turf | Fukushima | 350 | 1,600 |  |
| 21 | Early November | Fantasy Stakes | G3 | 1,400 m | Turf | Kyoto | 350 | 2,900 |  |
| 21 | Early November | Daily Hai Junior Stakes | G2 | 1,600 m | Turf | Kyoto | 375 | 3,800 |  |
| 21 | Early November | Keio Hai Junior Stakes | G2 | 1,400 m | Turf | Tokyo | 375 | 3,800 |  |
| 22 | Late November | Akamatsu Sho | Pre-OP | 1,600 m | Turf | Tokyo | 350 | 1,000 |  |
| 22 | Late November | Begonia Sho | Pre-OP | 1,600 m | Turf | Tokyo | 350 | 1,000 |  |
| 22 | Late November | Cattleya Sho | Pre-OP | 1,600 m | Dirt | Tokyo | 350 | 1,000 |  |
| 22 | Late November | Habotan Sho | Pre-OP | 2,000 m | Turf | Nakayama | 350 | 1,000 |  |
| 22 | Late November | Koyamaki Sho | Pre-OP | 1,600 m | Turf | Chukyo | 350 | 1,000 |  |
| 22 | Late November | Mochinoki Sho | Pre-OP | 1,800 m | Dirt | Kyoto | 350 | 1,000 |  |
| 22 | Late November | Shiragiku Sho | Pre-OP | 1,600 m | Turf | Kyoto | 350 | 1,000 |  |
| 22 | Late November | Shumeigiku Sho | Pre-OP | 1,400 m | Turf | Kyoto | 350 | 1,000 |  |
| 22 | Late November | Kyoto Junior Stakes | G3 | 2,000 m | Turf | Kyoto | 350 | 3,300 |  |
| 22 | Late November | Tokyo Sports Hai Junior Stakes | G3 | 1,800 m | Turf | Tokyo | 350 | 3,300 |  |
| 23 | Early December | Erica Sho | Pre-OP | 2,000 m | Turf | Hanshin | 350 | 1,000 |  |
| 23 | Early December | Hiiragi Sho | Pre-OP | 1,600 m | Turf | Nakayama | 350 | 1,000 |  |
| 23 | Early December | Kantsubaki Sho | Pre-OP | 1,400 m | Dirt | Chukyo | 350 | 1,000 |  |
| 23 | Early December | Kuromatsu Sho | Pre-OP | 1,200 m | Turf | Nakayama | 350 | 1,000 |  |
| 23 | Early December | Manryo Sho | Pre-OP | 1,400 m | Turf | Hanshin | 350 | 1,000 |  |
| 23 | Early December | Sazanka Sho | Pre-OP | 1,200 m | Turf | Hanshin | 350 | 1,000 |  |
| 23 | Early December | Tsuwabuki Sho | Pre-OP | 1,400 m | Turf | Chukyo | 350 | 1,000 |  |
| 23 | Early December | Asahi Hai Futurity Stakes | G1 | 1,600 m | Turf | Hanshin | 1,000 | 7,000 |  |
| 23 | Early December | Hanshin Juvenile Fillies | G1 | 1,600 m | Turf | Hanshin | 1,000 | 6,500 |  |
| 24 | Late December | Senryo Sho | Pre-OP | 1,600 m | Turf | Hanshin | 350 | 1,000 |  |
| 24 | Late December | Christmas Rose Stakes | OP | 1,200 m | Turf | Nakayama | 350 | 1,600 |  |
| 24 | Late December | Hopeful Stakes | G1 | 2,000 m | Turf | Nakayama | 1,000 | 7,000 |  |
| 24 | Late December | Zen-Nippon Junior Yushun | G1 | 1,600 m | Dirt | Kawasaki | 1,000 | ⚠️ | added by `pre_nar`; payout curve `54` absent from `en/race-fans` |


Junior graded slots, for reading the shape at a glance: two G2 (Daily Hai Junior Stakes and Keio Hai Junior Stakes, both Early November), nine G3 from Late July, and four G1 — Asahi Hai Futurity Stakes and Hanshin Juvenile Fillies in Early December, Hopeful Stakes and Zen-Nippon Junior Yushun in Late December. All 26 Pre-OP slots in the game are in this year.

## Classic Year

| Turn | Slot | Race | Tier | Distance | Surface | Track | Fans to enter | Fans for 1st | Notes |
|---|---|---|---|---|---|---|---|---|---|
| 1 | Early January | Junior Cup | OP | 1,600 m | Turf | Nakayama | 350 | 2,000 |  |
| 1 | Early January | Kobai Stakes | OP | 1,400 m | Turf | Kyoto | 350 | 2,000 |  |
| 1 | Early January | Fairy Stakes | G3 | 1,600 m | Turf | Nakayama | 750 | 3,500 |  |
| 1 | Early January | Keisei Hai | G3 | 2,000 m | Turf | Nakayama | 1,000 | 3,800 |  |
| 1 | Early January | Shinzan Kinen | G3 | 1,600 m | Turf | Kyoto | 1,000 | 3,800 |  |
| 2 | Late January | Crocus Stakes | OP | 1,400 m | Turf | Tokyo | 350 | 2,000 |  |
| 2 | Late January | Wakagoma Stakes | OP | 2,000 m | Turf | Kyoto | 350 | 2,000 |  |
| 3 | Early February | Elfin Stakes | OP | 1,600 m | Turf | Kyoto | 350 | 2,000 |  |
| 3 | Early February | Kisaragi Sho | G3 | 1,800 m | Turf | Kyoto | 1,000 | 3,800 |  |
| 3 | Early February | Kyodo News Hai | G3 | 1,800 m | Turf | Tokyo | 1,000 | 3,800 |  |
| 3 | Early February | Queen Cup | G3 | 1,600 m | Turf | Tokyo | 750 | 3,500 |  |
| 4 | Late February | Hyacinth Stakes | OP | 1,600 m | Dirt | Tokyo | 350 | 1,900 |  |
| 4 | Late February | Marguerite Stakes | OP | 1,200 m | Turf | Hanshin | 350 | 2,000 |  |
| 4 | Late February | Sumire Stakes | OP | 2,200 m | Turf | Hanshin | 350 | 2,000 |  |
| 5 | Early March | Anemone Stakes | OP | 1,600 m | Turf | Nakayama | 350 | 2,000 |  |
| 5 | Early March | Shoryu Stakes | OP | 1,400 m | Dirt | Chukyo | 350 | 1,800 |  |
| 5 | Early March | Fillies' Revue | G2 | 1,400 m | Turf | Hanshin | 1,750 | 5,200 |  |
| 5 | Early March | Tulip Sho | G2 | 1,600 m | Turf | Hanshin | 1,750 | 5,200 |  |
| 5 | Early March | Yayoi Sho | G2 | 2,000 m | Turf | Nakayama | 1,750 | 5,400 |  |
| 6 | Late March | Wakaba Stakes | OP | 2,000 m | Turf | Hanshin | 350 | 2,000 |  |
| 6 | Late March | Falcon Stakes | G3 | 1,400 m | Turf | Chukyo | 1,250 | 3,800 |  |
| 6 | Late March | Flower Cup | G3 | 1,800 m | Turf | Nakayama | 750 | 3,500 |  |
| 6 | Late March | Mainichi Hai | G3 | 1,800 m | Turf | Hanshin | 1,250 | 3,800 |  |
| 6 | Late March | Spring Stakes | G2 | 1,800 m | Turf | Nakayama | 1,750 | 5,400 |  |
| 7 | Early April | Fukuryu Stakes | OP | 1,800 m | Dirt | Nakayama | 350 | 1,800 |  |
| 7 | Early April | Wasurenagusa Sho | OP | 2,000 m | Turf | Hanshin | 350 | 2,000 |  |
| 7 | Early April | Arlington Cup | G3 | 1,600 m | Turf | Hanshin | 1,250 | 3,800 |  |
| 7 | Early April | Marine Cup | G3 | 1,600 m | Dirt | Funabashi | 1,000 | 2,500 | added by `pre_nar` |
| 7 | Early April | New Zealand Trophy | G2 | 1,600 m | Turf | Nakayama | 1,750 | 5,400 |  |
| 7 | Early April | Oka Sho | G1 | 1,600 m | Turf | Hanshin | 4,500 | 10,500 |  |
| 7 | Early April | Satsuki Sho | G1 | 2,000 m | Turf | Nakayama | 4,500 | 11,000 |  |
| 8 | Late April | Sweetpea Stakes | OP | 1,800 m | Turf | Tokyo | 350 | 2,000 |  |
| 8 | Late April | Tachibana Stakes | OP | 1,400 m | Turf | Kyoto | 350 | 2,000 |  |
| 8 | Late April | Tango Stakes | OP | 1,400 m | Dirt | Kyoto | 350 | 1,800 |  |
| 8 | Late April | Aoba Sho | G2 | 2,400 m | Turf | Tokyo | 1,800 | 5,400 |  |
| 8 | Late April | Flora Stakes | G2 | 2,000 m | Turf | Tokyo | 1,750 | 5,200 |  |
| 9 | Early May | Principal Stakes | OP | 2,000 m | Turf | Tokyo | 350 | 2,000 |  |
| 9 | Early May | Seiryu Stakes | OP | 1,600 m | Dirt | Tokyo | 350 | 1,800 |  |
| 9 | Early May | Kyoto Shimbun Hai | G2 | 2,200 m | Turf | Kyoto | 1,750 | 5,400 |  |
| 9 | Early May | NHK Mile Cup | G1 | 1,600 m | Turf | Tokyo | 5,000 | 10,500 |  |
| 10 | Late May | Hosu Stakes | OP | 1,800 m | Dirt | Kyoto | 350 | 1,900 |  |
| 10 | Late May | Shirayuri Stakes | OP | 1,800 m | Turf | Kyoto | 350 | 2,000 |  |
| 10 | Late May | Aoi Stakes | G3 | 1,200 m | Turf | Kyoto | 1,250 | 3,800 |  |
| 10 | Late May | Japanese Oaks | G1 | 2,400 m | Turf | Tokyo | 6,000 | 11,000 |  |
| 10 | Late May | Tokyo Yushun (Japanese Derby) | G1 | 2,400 m | Turf | Tokyo | 6,000 | 20,000 |  |
| 11 | Early June | Sleipnir Stakes | OP | 2,100 m | Dirt | Tokyo | 350 | 2,200 |  |
| 11 | Early June | Tempozan Stakes | OP | 1,400 m | Dirt | Hanshin | 350 | 2,200 |  |
| 11 | Early June | Epsom Cup | G3 | 1,800 m | Turf | Tokyo | 1,500 | 4,100 |  |
| 11 | Early June | Mermaid Stakes | G3 | 2,000 m | Turf | Hanshin | 1,000 | 3,600 |  |
| 11 | Early June | Naruo Kinen | G3 | 2,000 m | Turf | Hanshin | 1,500 | 4,100 |  |
| 11 | Early June | Kanto Oaks | G2 | 2,100 m | Dirt | Kawasaki | 1,800 | 3,500 | added by `pre_nar` |
| 11 | Early June | Yasuda Kinen | G1 | 1,600 m | Turf | Tokyo | 15,000 | 13,000 |  |
| 12 | Late June | Akhalteke Stakes | OP | 1,600 m | Dirt | Tokyo | 350 | 2,200 |  |
| 12 | Late June | Onuma Stakes | OP | 1,700 m | Dirt | Hakodate | 350 | 2,300 |  |
| 12 | Late June | Paradise Stakes | OP | 1,400 m | Turf | Tokyo | 350 | 2,500 |  |
| 12 | Late June | Sannomiya Stakes | OP | 1,800 m | Dirt | Hanshin | 350 | 2,200 |  |
| 12 | Late June | Yonago Stakes | OP | 1,600 m | Turf | Hanshin | 350 | 2,500 |  |
| 12 | Late June | Hakodate Sprint Stakes | G3 | 1,200 m | Turf | Hakodate | 1,250 | 3,900 |  |
| 12 | Late June | Unicorn Stakes | G3 | 1,600 m | Dirt | Tokyo | 750 | 3,500 |  |
| 12 | Late June | Takarazuka Kinen | G1 | 2,200 m | Turf | Hanshin | 20,000 | 15,000 |  |
| 13 | Early July | Marine Stakes | OP | 1,700 m | Dirt | Hakodate | 350 | 2,200 |  |
| 13 | Early July | Meitetsu Hai | OP | 1,800 m | Dirt | Chukyo | 350 | 2,300 |  |
| 13 | Early July | Tomoe Sho | OP | 1,800 m | Turf | Hakodate | 350 | 2,400 |  |
| 13 | Early July | CBC Sho | G3 | 1,200 m | Turf | Chukyo | 1,250 | 3,900 |  |
| 13 | Early July | Hakodate Kinen | G3 | 2,000 m | Turf | Hakodate | 1,500 | 4,100 |  |
| 13 | Early July | Procyon Stakes | G3 | 1,400 m | Dirt | Chukyo | 1,000 | 3,600 |  |
| 13 | Early July | Radio Nikkei Sho | G3 | 1,800 m | Turf | Fukushima | 1,250 | 3,800 |  |
| 13 | Early July | Sparking Lady Cup | G3 | 1,600 m | Dirt | Kawasaki | 1,000 | 2,500 | added by `pre_nar` |
| 13 | Early July | Tanabata Sho | G3 | 2,000 m | Turf | Fukushima | 1,500 | 4,100 |  |
| 13 | Early July | Japan Dirt Derby | G1 | 2,000 m | Dirt | Ooi | 4,000 | 4,500 |  |
| 14 | Late July | Fukushima TV Open | OP | 1,200 m | Turf | Fukushima | 350 | 2,300 |  |
| 14 | Late July | Chukyo Kinen | G3 | 1,600 m | Turf | Chukyo | 1,250 | 3,900 |  |
| 14 | Late July | Ibis Summer Dash | G3 | 1,000 m | Turf | Niigata | 1,250 | 3,900 |  |
| 14 | Late July | Mercury Cup | G3 | 2,000 m | Dirt | Morioka | 1,000 | 2,300 | added by `pre_nar` |
| 14 | Late July | Queen Stakes | G3 | 1,800 m | Turf | Sapporo | 1,000 | 3,600 |  |
| 15 | Early August | Aso Stakes | OP | 1,700 m | Dirt | Kokura | 350 | 2,200 |  |
| 15 | Early August | Kanetsu Stakes | OP | 1,800 m | Turf | Niigata | 350 | 2,400 |  |
| 15 | Early August | Sapporo Nikkei Open | OP | 2,600 m | Turf | Sapporo | 350 | 2,600 |  |
| 15 | Early August | UHB Sho | OP | 1,200 m | Turf | Sapporo | 350 | 2,300 |  |
| 15 | Early August | Elm Stakes | G3 | 1,700 m | Dirt | Sapporo | 1,000 | 3,600 |  |
| 15 | Early August | Kokura Kinen | G3 | 2,000 m | Turf | Kokura | 1,500 | 4,100 |  |
| 15 | Early August | Leopard Stakes | G3 | 1,800 m | Dirt | Niigata | 1,250 | 4,000 |  |
| 15 | Early August | Sekiya Kinen | G3 | 1,600 m | Turf | Niigata | 1,250 | 3,900 |  |
| 16 | Late August | BSN Sho | OP | 1,800 m | Dirt | Niigata | 350 | 2,300 |  |
| 16 | Late August | Kokura Nikkei Open | OP | 1,800 m | Turf | Kokura | 350 | 2,400 |  |
| 16 | Late August | NST Sho | OP | 1,200 m | Dirt | Niigata | 350 | 2,200 |  |
| 16 | Late August | Toki Stakes | OP | 1,400 m | Turf | Niigata | 350 | 2,500 |  |
| 16 | Late August | Cluster Cup | G3 | 1,200 m | Dirt | Morioka | 1,000 | 2,300 | added by `pre_nar` |
| 16 | Late August | Keeneland Cup | G3 | 1,200 m | Turf | Sapporo | 1,500 | 4,100 |  |
| 16 | Late August | Kitakyushu Kinen | G3 | 1,200 m | Turf | Kokura | 1,250 | 3,900 |  |
| 16 | Late August | Sapporo Kinen | G2 | 2,000 m | Turf | Sapporo | 2,000 | 7,000 |  |
| 17 | Early September | Enif Stakes | OP | 1,400 m | Dirt | Hanshin | 350 | 2,300 |  |
| 17 | Early September | Radio Nippon Sho | OP | 1,800 m | Dirt | Nakayama | 350 | 2,200 |  |
| 17 | Early September | Tancho Stakes | OP | 2,600 m | Turf | Sapporo | 350 | 2,400 |  |
| 17 | Early September | Keisei Hai Autumn Handicap | G3 | 1,600 m | Turf | Nakayama | 1,250 | 3,900 |  |
| 17 | Early September | Niigata Kinen | G3 | 2,000 m | Turf | Niigata | 1,500 | 4,100 |  |
| 17 | Early September | Shion Stakes | G3 | 2,000 m | Turf | Nakayama | 1,000 | 3,500 |  |
| 17 | Early September | Centaur Stakes | G2 | 1,200 m | Turf | Hanshin | 1,900 | 5,900 |  |
| 17 | Early September | Prix Niel | G2 | 2,400 m | Turf | Longchamp | 2,000 | ⚠️ | **not on Global**; added by `pre_2_5th_anni`; payout curve `56` absent from `en/race-fans` |
| 17 | Early September | Rose Stakes | G2 | 1,800 m | Turf | Hanshin | 1,750 | 5,200 |  |
| 18 | Late September | Nagatsuki Stakes | OP | 1,200 m | Dirt | Nakayama | 350 | 2,200 |  |
| 18 | Late September | Port Island Stakes | OP | 1,600 m | Turf | Hanshin | 350 | 2,500 |  |
| 18 | Late September | Sirius Stakes | G3 | 2,000 m | Dirt | Hanshin | 1,000 | 3,600 |  |
| 18 | Late September | All Comers | G2 | 2,200 m | Turf | Nakayama | 2,000 | 6,700 |  |
| 18 | Late September | Kobe Shimbun Hai | G2 | 2,400 m | Turf | Hanshin | 1,750 | 5,400 |  |
| 18 | Late September | Sazanka TV Hai | G2 | 1,800 m | Dirt | Funabashi | 1,800 | ⚠️ | added by `pre_nar`; payout curve `51` absent from `en/race-fans` |
| 18 | Late September | St. Lite Kinen | G2 | 2,200 m | Turf | Nakayama | 1,750 | 5,400 |  |
| 18 | Late September | Sprinters Stakes | G1 | 1,200 m | Turf | Nakayama | 15,000 | 13,000 |  |
| 19 | Early October | Green Channel Cup | OP | 1,400 m | Dirt | Tokyo | 350 | 2,300 |  |
| 19 | Early October | October Stakes | OP | 2,000 m | Turf | Tokyo | 350 | 2,600 |  |
| 19 | Early October | Opal Stakes | OP | 1,200 m | Turf | Kyoto | 350 | 2,500 |  |
| 19 | Early October | Shinetsu Stakes | OP | 1,400 m | Turf | Niigata | 350 | 2,500 |  |
| 19 | Early October | Uzumasa Stakes | OP | 1,800 m | Dirt | Kyoto | 350 | 2,200 |  |
| 19 | Early October | Fuchu Umamusume Stakes | G2 | 1,800 m | Turf | Tokyo | 1,800 | 5,500 |  |
| 19 | Early October | Kyoto Daishoten | G2 | 2,400 m | Turf | Kyoto | 2,000 | 6,700 |  |
| 19 | Early October | Ladies' Prelude | G2 | 1,800 m | Dirt | Ooi | 1,800 | 3,100 | added by `pre_nar` |
| 19 | Early October | Mainichi Okan | G2 | 1,800 m | Turf | Tokyo | 2,000 | 6,700 |  |
| 19 | Early October | Tokyo Hai | G2 | 1,200 m | Dirt | Ooi | 1,800 | 3,500 | added by `pre_nar` |
| 19 | Early October | M.C. Nambu Hai | G1 | 1,600 m | Dirt | Morioka | 12,000 | 6,000 | added by `pre_nar` |
| 20 | Late October | Brazil Cup | OP | 2,100 m | Dirt | Tokyo | 350 | 2,300 |  |
| 20 | Late October | Cassiopeia Stakes | OP | 1,800 m | Turf | Kyoto | 350 | 2,600 |  |
| 20 | Late October | Lumiere Autumn Dash | OP | 1,000 m | Turf | Niigata | 350 | 2,500 |  |
| 20 | Late October | Muromachi Stakes | OP | 1,200 m | Dirt | Kyoto | 350 | 2,200 |  |
| 20 | Late October | Fuji Stakes | G2 | 1,600 m | Turf | Tokyo | 1,900 | 5,900 |  |
| 20 | Late October | Swan Stakes | G2 | 1,400 m | Turf | Kyoto | 1,900 | 5,900 |  |
| 20 | Late October | Kikuka Sho | G1 | 3,000 m | Turf | Kyoto | 7,500 | 12,000 |  |
| 20 | Late October | Shuka Sho | G1 | 2,000 m | Turf | Kyoto | 7,500 | 10,000 |  |
| 20 | Late October | Tenno Sho (Autumn) | G1 | 2,000 m | Turf | Tokyo | 20,000 | 15,000 |  |
| 21 | Early November | Oro Cup | OP | 1,400 m | Turf | Tokyo | 350 | 2,500 |  |
| 21 | Early November | Fukushima Kinen | G3 | 2,000 m | Turf | Fukushima | 1,500 | 4,100 |  |
| 21 | Early November | Miyako Stakes | G3 | 1,800 m | Dirt | Kyoto | 750 | 3,800 |  |
| 21 | Early November | Musashino Stakes | G3 | 1,600 m | Dirt | Tokyo | 1,250 | 3,800 |  |
| 21 | Early November | Copa Republica Argentina | G2 | 2,500 m | Turf | Tokyo | 1,900 | 5,700 |  |
| 21 | Early November | JBC Classic | G1 | 2,000 m | Dirt | Ooi | 12,000 | 8,000 |  |
| 21 | Early November | JBC Ladies’ Classic | G1 | 1,800 m | Dirt | Ooi | 12,000 | 4,100 |  |
| 21 | Early November | JBC Sprint | G1 | 1,200 m | Dirt | Ooi | 12,000 | 6,000 |  |
| 21 | Early November | Queen Elizabeth II Cup | G1 | 2,200 m | Turf | Kyoto | 10,000 | 10,500 |  |
| 22 | Late November | Andromeda Stakes | OP | 2,000 m | Turf | Kyoto | 350 | 2,600 |  |
| 22 | Late November | Autumn Leaf Stakes | OP | 1,200 m | Dirt | Kyoto | 350 | 2,200 |  |
| 22 | Late November | Capital Stakes | OP | 1,600 m | Turf | Tokyo | 350 | 2,500 |  |
| 22 | Late November | Fukushima Minyu Cup | OP | 1,700 m | Dirt | Fukushima | 350 | 2,300 |  |
| 22 | Late November | Shimotsuki Stakes | OP | 1,400 m | Dirt | Tokyo | 350 | 2,200 |  |
| 22 | Late November | Keihan Hai | G3 | 1,200 m | Turf | Kyoto | 1,250 | 3,900 |  |
| 22 | Late November | Japan Cup | G1 | 2,400 m | Turf | Tokyo | 25,000 | 30,000 |  |
| 22 | Late November | Mile Championship | G1 | 1,600 m | Turf | Kyoto | 15,000 | 11,000 |  |
| 23 | Early December | December Stakes | OP | 1,800 m | Turf | Nakayama | 350 | 2,600 |  |
| 23 | Early December | Lapis Lazuli Stakes | OP | 1,200 m | Turf | Nakayama | 350 | 2,500 |  |
| 23 | Early December | Rigel Stakes | OP | 1,600 m | Turf | Hanshin | 350 | 2,500 |  |
| 23 | Early December | Shiwasu Stakes | OP | 1,800 m | Dirt | Nakayama | 350 | 2,300 |  |
| 23 | Early December | Tanzanite Stakes | OP | 1,200 m | Turf | Hanshin | 350 | 2,300 |  |
| 23 | Early December | Capella Stakes | G3 | 1,200 m | Dirt | Nakayama | 1,000 | 3,600 |  |
| 23 | Early December | Challenge Cup | G3 | 2,000 m | Turf | Hanshin | 1,500 | 4,100 |  |
| 23 | Early December | Chunichi Shimbun Hai | G3 | 2,000 m | Turf | Chukyo | 1,500 | 4,100 |  |
| 23 | Early December | Queen Sho | G3 | 1,800 m | Dirt | Funabashi | 1,000 | ⚠️ | added by `pre_nar`; payout curve `53` absent from `en/race-fans` |
| 23 | Early December | Turquoise Stakes | G3 | 1,600 m | Turf | Nakayama | 1,000 | 3,600 |  |
| 23 | Early December | Stayers Stakes | G2 | 3,600 m | Turf | Nakayama | 2,000 | 6,200 |  |
| 23 | Early December | Champions Cup | G1 | 1,800 m | Dirt | Chukyo | 12,000 | 10,000 |  |
| 24 | Late December | Betelgeuse Stakes | OP | 1,800 m | Dirt | Hanshin | 350 | 2,200 |  |
| 24 | Late December | Galaxy Stakes | OP | 1,400 m | Dirt | Hanshin | 350 | 2,200 |  |
| 24 | Late December | Hanshin Cup | G2 | 1,400 m | Turf | Hanshin | 2,000 | 6,700 |  |
| 24 | Late December | Arima Kinen | G1 | 2,500 m | Turf | Nakayama | 25,000 | 30,000 |  |
| 24 | Late December | Tokyo Daishoten | G1 | 2,000 m | Dirt | Ooi | 12,000 | 8,000 |  |


## Senior Year

| Turn | Slot | Race | Tier | Distance | Surface | Track | Fans to enter | Fans for 1st | Notes |
|---|---|---|---|---|---|---|---|---|---|
| 1 | Early January | Carbuncle Stakes | OP | 1,200 m | Turf | Nakayama | 350 | 2,300 |  |
| 1 | Early January | January Stakes | OP | 1,200 m | Dirt | Nakayama | 350 | 2,200 |  |
| 1 | Early January | Manyo Stakes | OP | 3,000 m | Turf | Kyoto | 350 | 2,400 |  |
| 1 | Early January | New Year Stakes | OP | 1,600 m | Turf | Nakayama | 350 | 2,500 |  |
| 1 | Early January | Pollux Stakes | OP | 1,800 m | Dirt | Nakayama | 350 | 2,200 |  |
| 1 | Early January | Yodo Tankyori Stakes | OP | 1,200 m | Turf | Kyoto | 350 | 2,500 |  |
| 1 | Early January | Aichi Hai | G3 | 2,000 m | Turf | Chukyo | 750 | 3,600 |  |
| 1 | Early January | Kyoto Kimpai | G3 | 1,600 m | Turf | Kyoto | 1,500 | 4,100 |  |
| 1 | Early January | Nakayama Kimpai | G3 | 2,000 m | Turf | Nakayama | 1,500 | 4,100 |  |
| 1 | Early January | Nikkei Shinshun Hai | G2 | 2,400 m | Turf | Kyoto | 1,800 | 5,700 |  |
| 2 | Late January | Shirafuji Stakes | OP | 2,000 m | Turf | Tokyo | 350 | 2,600 |  |
| 2 | Late January | Subaru Stakes | OP | 1,400 m | Dirt | Kyoto | 350 | 2,300 |  |
| 2 | Late January | Negishi Stakes | G3 | 1,400 m | Dirt | Tokyo | 1,000 | 3,800 |  |
| 2 | Late January | Silk Road Stakes | G3 | 1,200 m | Turf | Kyoto | 1,250 | 3,900 |  |
| 2 | Late January | TCK Jo-o Hai | G3 | 1,800 m | Dirt | Ooi | 1,000 | 2,200 | added by `pre_nar` |
| 2 | Late January | American JCC | G2 | 2,200 m | Turf | Nakayama | 1,900 | 6,200 |  |
| 2 | Late January | Tokai Stakes | G2 | 1,800 m | Dirt | Chukyo | 1,800 | 5,500 |  |
| 3 | Early February | Aldebaran Stakes | OP | 1,900 m | Dirt | Kyoto | 350 | 2,200 |  |
| 3 | Early February | Rakuyo Stakes | OP | 1,600 m | Turf | Kyoto | 350 | 2,500 |  |
| 3 | Early February | Valentine Stakes | OP | 1,400 m | Dirt | Tokyo | 350 | 2,200 |  |
| 3 | Early February | Yamato Stakes | OP | 1,200 m | Dirt | Kyoto | 350 | 2,200 |  |
| 3 | Early February | Tokyo Shimbun Hai | G3 | 1,600 m | Turf | Tokyo | 1,250 | 3,900 |  |
| 3 | Early February | Kyoto Kinen | G2 | 2,200 m | Turf | Kyoto | 1,900 | 6,200 |  |
| 3 | Early February | Kawasaki Kinen | G1 | 2,100 m | Dirt | Kawasaki | 12,000 | 6,000 | added by `pre_nar` |
| 4 | Late February | Kitakyushu Tankyori Stakes | OP | 1,200 m | Turf | Kokura | 350 | 2,300 |  |
| 4 | Late February | Sobu Stakes | OP | 1,800 m | Dirt | Nakayama | 350 | 2,200 |  |
| 4 | Late February | Diamond Stakes | G3 | 3,400 m | Turf | Tokyo | 1,500 | 4,100 |  |
| 4 | Late February | Hankyu Hai | G3 | 1,400 m | Turf | Hanshin | 1,500 | 4,100 |  |
| 4 | Late February | Kokura Daishoten | G3 | 1,800 m | Turf | Kokura | 1,500 | 4,100 |  |
| 4 | Late February | Kyoto Umamusume Stakes | G3 | 1,400 m | Turf | Kyoto | 750 | 3,600 |  |
| 4 | Late February | Nakayama Kinen | G2 | 1,800 m | Turf | Nakayama | 1,900 | 6,700 |  |
| 4 | Late February | February Stakes | G1 | 1,600 m | Dirt | Tokyo | 12,000 | 10,000 |  |
| 5 | Early March | Kochi Stakes | OP | 1,600 m | Turf | Nakayama | 350 | 2,500 |  |
| 5 | Early March | Nigawa Stakes | OP | 2,000 m | Dirt | Hanshin | 350 | 2,300 |  |
| 5 | Early March | Osakajo Stakes | OP | 1,800 m | Turf | Hanshin | 350 | 2,600 |  |
| 5 | Early March | Polaris Stakes | OP | 1,400 m | Dirt | Hanshin | 350 | 2,200 |  |
| 5 | Early March | Nakayama Umamusume Stakes | G3 | 1,800 m | Turf | Nakayama | 1,000 | 3,600 |  |
| 5 | Early March | Ocean Stakes | G3 | 1,200 m | Turf | Nakayama | 1,500 | 4,100 |  |
| 5 | Early March | Empress Hai | G2 | 2,100 m | Dirt | Kawasaki | 1,800 | 3,500 | added by `pre_nar` |
| 5 | Early March | Kinko Sho | G2 | 2,000 m | Turf | Chukyo | 2,000 | 6,700 |  |
| 6 | Late March | Chiba Stakes | OP | 1,200 m | Dirt | Nakayama | 350 | 2,200 |  |
| 6 | Late March | Rokko Stakes | OP | 1,600 m | Turf | Hanshin | 350 | 2,500 |  |
| 6 | Late March | March Stakes | G3 | 1,800 m | Dirt | Nakayama | 1,000 | 3,600 |  |
| 6 | Late March | Diolite Kinen | G2 | 2,400 m | Dirt | Funabashi | 1,800 | ⚠️ | added by `pre_nar`; payout curve `51` absent from `en/race-fans` |
| 6 | Late March | Hanshin Daishoten | G2 | 3,000 m | Turf | Hanshin | 2,000 | 6,700 |  |
| 6 | Late March | Nikkei Sho | G2 | 2,500 m | Turf | Nakayama | 2,000 | 6,700 |  |
| 6 | Late March | Osaka Hai | G1 | 2,000 m | Turf | Hanshin | 20,000 | 13,500 |  |
| 6 | Late March | Takamatsunomiya Kinen | G1 | 1,200 m | Turf | Chukyo | 15,000 | 13,000 |  |
| 7 | Early April | Azumakofuji Stakes | OP | 1,700 m | Dirt | Fukushima | 350 | 2,200 |  |
| 7 | Early April | Coral Stakes | OP | 1,400 m | Dirt | Hanshin | 350 | 2,300 |  |
| 7 | Early April | Fukushima Mimpo Hai | OP | 2,000 m | Turf | Fukushima | 350 | 2,600 |  |
| 7 | Early April | Keiyo Stakes | OP | 1,200 m | Dirt | Nakayama | 350 | 2,300 |  |
| 7 | Early April | Shunrai Stakes | OP | 1,200 m | Turf | Nakayama | 350 | 2,500 |  |
| 7 | Early April | Antares Stakes | G3 | 1,800 m | Dirt | Hanshin | 1,000 | 3,600 |  |
| 7 | Early April | Lord Derby Challenge Trophy | G3 | 1,600 m | Turf | Nakayama | 1,250 | 3,900 |  |
| 7 | Early April | Marine Cup | G3 | 1,600 m | Dirt | Funabashi | 1,000 | 2,500 | added by `pre_nar` |
| 7 | Early April | Hanshin Umamusume Stakes | G2 | 1,600 m | Turf | Hanshin | 1,800 | 5,500 |  |
| 8 | Late April | Oasis Stakes | OP | 1,600 m | Dirt | Tokyo | 350 | 2,300 |  |
| 8 | Late April | Tennozan Stakes | OP | 1,200 m | Dirt | Kyoto | 350 | 2,200 |  |
| 8 | Late April | Fukushima Umamusume Stakes | G3 | 1,800 m | Turf | Fukushima | 1,250 | 3,800 |  |
| 8 | Late April | Tokyo Sprint | G3 | 1,200 m | Dirt | Ooi | 1,000 | ⚠️ | added by `pre_nar`; payout curve `52` absent from `en/race-fans` |
| 8 | Late April | Milers Cup | G2 | 1,600 m | Turf | Kyoto | 1,900 | 5,900 |  |
| 8 | Late April | Tenno Sho (Spring) | G1 | 3,200 m | Turf | Kyoto | 20,000 | 15,000 |  |
| 9 | Early May | Brilliant Stakes | OP | 2,100 m | Dirt | Tokyo | 350 | 2,300 |  |
| 9 | Early May | Kurama Stakes | OP | 1,200 m | Turf | Kyoto | 350 | 2,300 |  |
| 9 | Early May | Metropolitan Stakes | OP | 2,400 m | Turf | Tokyo | 350 | 2,600 |  |
| 9 | Early May | Miyakooji Stakes | OP | 1,800 m | Turf | Kyoto | 350 | 2,600 |  |
| 9 | Early May | Ritto Stakes | OP | 1,400 m | Dirt | Kyoto | 350 | 2,300 |  |
| 9 | Early May | Tanigawadake Stakes | OP | 1,600 m | Turf | Niigata | 350 | 2,500 |  |
| 9 | Early May | Niigata Daishoten | G3 | 2,000 m | Turf | Niigata | 1,500 | 4,100 |  |
| 9 | Early May | Keio Hai Spring Cup | G2 | 1,400 m | Turf | Tokyo | 1,900 | 5,900 |  |
| 9 | Early May | Kashiwa Kinen | G1 | 1,600 m | Dirt | Funabashi | 12,000 | 8,000 | added by `pre_nar` |
| 9 | Early May | Victoria Mile | G1 | 1,600 m | Turf | Tokyo | 10,000 | 10,500 |  |
| 10 | Late May | Azuchijo Stakes | OP | 1,400 m | Turf | Kyoto | 350 | 2,500 |  |
| 10 | Late May | Idaten Stakes | OP | 1,000 m | Turf | Niigata | 350 | 2,300 |  |
| 10 | Late May | Keyaki Stakes | OP | 1,400 m | Dirt | Tokyo | 350 | 2,200 |  |
| 10 | Late May | May Stakes | OP | 1,800 m | Turf | Tokyo | 350 | 2,400 |  |
| 10 | Late May | Heian Stakes | G3 | 1,900 m | Dirt | Kyoto | 1,000 | 3,600 |  |
| 10 | Late May | Meguro Kinen | G2 | 2,500 m | Turf | Tokyo | 1,800 | 5,700 |  |
| 11 | Early June | Sleipnir Stakes | OP | 2,100 m | Dirt | Tokyo | 350 | 2,200 |  |
| 11 | Early June | Tempozan Stakes | OP | 1,400 m | Dirt | Hanshin | 350 | 2,200 |  |
| 11 | Early June | Epsom Cup | G3 | 1,800 m | Turf | Tokyo | 1,500 | 4,100 |  |
| 11 | Early June | Mermaid Stakes | G3 | 2,000 m | Turf | Hanshin | 1,000 | 3,600 |  |
| 11 | Early June | Naruo Kinen | G3 | 2,000 m | Turf | Hanshin | 1,500 | 4,100 |  |
| 11 | Early June | Yasuda Kinen | G1 | 1,600 m | Turf | Tokyo | 15,000 | 13,000 |  |
| 12 | Late June | Akhalteke Stakes | OP | 1,600 m | Dirt | Tokyo | 350 | 2,200 |  |
| 12 | Late June | Onuma Stakes | OP | 1,700 m | Dirt | Hakodate | 350 | 2,300 |  |
| 12 | Late June | Paradise Stakes | OP | 1,400 m | Turf | Tokyo | 350 | 2,500 |  |
| 12 | Late June | Sannomiya Stakes | OP | 1,800 m | Dirt | Hanshin | 350 | 2,200 |  |
| 12 | Late June | Yonago Stakes | OP | 1,600 m | Turf | Hanshin | 350 | 2,500 |  |
| 12 | Late June | Hakodate Sprint Stakes | G3 | 1,200 m | Turf | Hakodate | 1,250 | 3,900 |  |
| 12 | Late June | Takarazuka Kinen | G1 | 2,200 m | Turf | Hanshin | 20,000 | 15,000 |  |
| 12 | Late June | Teio Sho | G1 | 2,000 m | Dirt | Ooi | 12,000 | 6,000 |  |
| 13 | Early July | Marine Stakes | OP | 1,700 m | Dirt | Hakodate | 350 | 2,200 |  |
| 13 | Early July | Meitetsu Hai | OP | 1,800 m | Dirt | Chukyo | 350 | 2,300 |  |
| 13 | Early July | Tomoe Sho | OP | 1,800 m | Turf | Hakodate | 350 | 2,400 |  |
| 13 | Early July | CBC Sho | G3 | 1,200 m | Turf | Chukyo | 1,250 | 3,900 |  |
| 13 | Early July | Hakodate Kinen | G3 | 2,000 m | Turf | Hakodate | 1,500 | 4,100 |  |
| 13 | Early July | Procyon Stakes | G3 | 1,400 m | Dirt | Chukyo | 1,000 | 3,600 |  |
| 13 | Early July | Sparking Lady Cup | G3 | 1,600 m | Dirt | Kawasaki | 1,000 | 2,500 | added by `pre_nar` |
| 13 | Early July | Tanabata Sho | G3 | 2,000 m | Turf | Fukushima | 1,500 | 4,100 |  |
| 14 | Late July | Fukushima TV Open | OP | 1,200 m | Turf | Fukushima | 350 | 2,300 |  |
| 14 | Late July | Chukyo Kinen | G3 | 1,600 m | Turf | Chukyo | 1,250 | 3,900 |  |
| 14 | Late July | Ibis Summer Dash | G3 | 1,000 m | Turf | Niigata | 1,250 | 3,900 |  |
| 14 | Late July | Mercury Cup | G3 | 2,000 m | Dirt | Morioka | 1,000 | 2,300 | added by `pre_nar` |
| 14 | Late July | Queen Stakes | G3 | 1,800 m | Turf | Sapporo | 1,000 | 3,600 |  |
| 15 | Early August | Aso Stakes | OP | 1,700 m | Dirt | Kokura | 350 | 2,200 |  |
| 15 | Early August | Kanetsu Stakes | OP | 1,800 m | Turf | Niigata | 350 | 2,400 |  |
| 15 | Early August | Sapporo Nikkei Open | OP | 2,600 m | Turf | Sapporo | 350 | 2,600 |  |
| 15 | Early August | UHB Sho | OP | 1,200 m | Turf | Sapporo | 350 | 2,300 |  |
| 15 | Early August | Elm Stakes | G3 | 1,700 m | Dirt | Sapporo | 1,000 | 3,600 |  |
| 15 | Early August | Kokura Kinen | G3 | 2,000 m | Turf | Kokura | 1,500 | 4,100 |  |
| 15 | Early August | Sekiya Kinen | G3 | 1,600 m | Turf | Niigata | 1,250 | 3,900 |  |
| 16 | Late August | BSN Sho | OP | 1,800 m | Dirt | Niigata | 350 | 2,300 |  |
| 16 | Late August | Kokura Nikkei Open | OP | 1,800 m | Turf | Kokura | 350 | 2,400 |  |
| 16 | Late August | NST Sho | OP | 1,200 m | Dirt | Niigata | 350 | 2,200 |  |
| 16 | Late August | Toki Stakes | OP | 1,400 m | Turf | Niigata | 350 | 2,500 |  |
| 16 | Late August | Cluster Cup | G3 | 1,200 m | Dirt | Morioka | 1,000 | 2,300 | added by `pre_nar` |
| 16 | Late August | Keeneland Cup | G3 | 1,200 m | Turf | Sapporo | 1,500 | 4,100 |  |
| 16 | Late August | Kitakyushu Kinen | G3 | 1,200 m | Turf | Kokura | 1,250 | 3,900 |  |
| 16 | Late August | Sapporo Kinen | G2 | 2,000 m | Turf | Sapporo | 2,000 | 7,000 |  |
| 17 | Early September | Enif Stakes | OP | 1,400 m | Dirt | Hanshin | 350 | 2,300 |  |
| 17 | Early September | Radio Nippon Sho | OP | 1,800 m | Dirt | Nakayama | 350 | 2,200 |  |
| 17 | Early September | Tancho Stakes | OP | 2,600 m | Turf | Sapporo | 350 | 2,400 |  |
| 17 | Early September | Keisei Hai Autumn Handicap | G3 | 1,600 m | Turf | Nakayama | 1,250 | 3,900 |  |
| 17 | Early September | Niigata Kinen | G3 | 2,000 m | Turf | Niigata | 1,500 | 4,100 |  |
| 17 | Early September | Centaur Stakes | G2 | 1,200 m | Turf | Hanshin | 1,900 | 5,900 |  |
| 17 | Early September | Prix Foy | G2 | 2,400 m | Turf | Longchamp | 2,000 | ⚠️ | **not on Global**; added by `pre_2_5th_anni`; payout curve `56` absent from `en/race-fans` |
| 18 | Late September | Nagatsuki Stakes | OP | 1,200 m | Dirt | Nakayama | 350 | 2,200 |  |
| 18 | Late September | Port Island Stakes | OP | 1,600 m | Turf | Hanshin | 350 | 2,500 |  |
| 18 | Late September | Sirius Stakes | G3 | 2,000 m | Dirt | Hanshin | 1,000 | 3,600 |  |
| 18 | Late September | All Comers | G2 | 2,200 m | Turf | Nakayama | 2,000 | 6,700 |  |
| 18 | Late September | Sazanka TV Hai | G2 | 1,800 m | Dirt | Funabashi | 1,800 | ⚠️ | added by `pre_nar`; payout curve `51` absent from `en/race-fans` |
| 18 | Late September | Sprinters Stakes | G1 | 1,200 m | Turf | Nakayama | 15,000 | 13,000 |  |
| 19 | Early October | Green Channel Cup | OP | 1,400 m | Dirt | Tokyo | 350 | 2,300 |  |
| 19 | Early October | October Stakes | OP | 2,000 m | Turf | Tokyo | 350 | 2,600 |  |
| 19 | Early October | Opal Stakes | OP | 1,200 m | Turf | Kyoto | 350 | 2,500 |  |
| 19 | Early October | Shinetsu Stakes | OP | 1,400 m | Turf | Niigata | 350 | 2,500 |  |
| 19 | Early October | Uzumasa Stakes | OP | 1,800 m | Dirt | Kyoto | 350 | 2,200 |  |
| 19 | Early October | Fuchu Umamusume Stakes | G2 | 1,800 m | Turf | Tokyo | 1,800 | 5,500 |  |
| 19 | Early October | Kyoto Daishoten | G2 | 2,400 m | Turf | Kyoto | 2,000 | 6,700 |  |
| 19 | Early October | Ladies' Prelude | G2 | 1,800 m | Dirt | Ooi | 1,800 | 3,100 | added by `pre_nar` |
| 19 | Early October | Mainichi Okan | G2 | 1,800 m | Turf | Tokyo | 2,000 | 6,700 |  |
| 19 | Early October | Tokyo Hai | G2 | 1,200 m | Dirt | Ooi | 1,800 | 3,500 | added by `pre_nar` |
| 19 | Early October | M.C. Nambu Hai | G1 | 1,600 m | Dirt | Morioka | 12,000 | 6,000 | added by `pre_nar` |
| 20 | Late October | Brazil Cup | OP | 2,100 m | Dirt | Tokyo | 350 | 2,300 |  |
| 20 | Late October | Cassiopeia Stakes | OP | 1,800 m | Turf | Kyoto | 350 | 2,600 |  |
| 20 | Late October | Lumiere Autumn Dash | OP | 1,000 m | Turf | Niigata | 350 | 2,500 |  |
| 20 | Late October | Muromachi Stakes | OP | 1,200 m | Dirt | Kyoto | 350 | 2,200 |  |
| 20 | Late October | Fuji Stakes | G2 | 1,600 m | Turf | Tokyo | 1,900 | 5,900 |  |
| 20 | Late October | Swan Stakes | G2 | 1,400 m | Turf | Kyoto | 1,900 | 5,900 |  |
| 20 | Late October | Tenno Sho (Autumn) | G1 | 2,000 m | Turf | Tokyo | 20,000 | 15,000 |  |
| 21 | Early November | Oro Cup | OP | 1,400 m | Turf | Tokyo | 350 | 2,500 |  |
| 21 | Early November | Fukushima Kinen | G3 | 2,000 m | Turf | Fukushima | 1,500 | 4,100 |  |
| 21 | Early November | Miyako Stakes | G3 | 1,800 m | Dirt | Kyoto | 750 | 3,800 |  |
| 21 | Early November | Musashino Stakes | G3 | 1,600 m | Dirt | Tokyo | 1,250 | 3,800 |  |
| 21 | Early November | Copa Republica Argentina | G2 | 2,500 m | Turf | Tokyo | 1,900 | 5,700 |  |
| 21 | Early November | JBC Classic | G1 | 2,000 m | Dirt | Ooi | 12,000 | 8,000 |  |
| 21 | Early November | JBC Ladies’ Classic | G1 | 1,800 m | Dirt | Ooi | 12,000 | 4,100 |  |
| 21 | Early November | JBC Sprint | G1 | 1,200 m | Dirt | Ooi | 12,000 | 6,000 |  |
| 21 | Early November | Queen Elizabeth II Cup | G1 | 2,200 m | Turf | Kyoto | 10,000 | 10,500 |  |
| 22 | Late November | Andromeda Stakes | OP | 2,000 m | Turf | Kyoto | 350 | 2,600 |  |
| 22 | Late November | Autumn Leaf Stakes | OP | 1,200 m | Dirt | Kyoto | 350 | 2,200 |  |
| 22 | Late November | Capital Stakes | OP | 1,600 m | Turf | Tokyo | 350 | 2,500 |  |
| 22 | Late November | Fukushima Minyu Cup | OP | 1,700 m | Dirt | Fukushima | 350 | 2,300 |  |
| 22 | Late November | Shimotsuki Stakes | OP | 1,400 m | Dirt | Tokyo | 350 | 2,200 |  |
| 22 | Late November | Keihan Hai | G3 | 1,200 m | Turf | Kyoto | 1,250 | 3,900 |  |
| 22 | Late November | Japan Cup | G1 | 2,400 m | Turf | Tokyo | 25,000 | 30,000 |  |
| 22 | Late November | Mile Championship | G1 | 1,600 m | Turf | Kyoto | 15,000 | 11,000 |  |
| 23 | Early December | December Stakes | OP | 1,800 m | Turf | Nakayama | 350 | 2,600 |  |
| 23 | Early December | Lapis Lazuli Stakes | OP | 1,200 m | Turf | Nakayama | 350 | 2,500 |  |
| 23 | Early December | Rigel Stakes | OP | 1,600 m | Turf | Hanshin | 350 | 2,500 |  |
| 23 | Early December | Shiwasu Stakes | OP | 1,800 m | Dirt | Nakayama | 350 | 2,300 |  |
| 23 | Early December | Tanzanite Stakes | OP | 1,200 m | Turf | Hanshin | 350 | 2,300 |  |
| 23 | Early December | Capella Stakes | G3 | 1,200 m | Dirt | Nakayama | 1,000 | 3,600 |  |
| 23 | Early December | Challenge Cup | G3 | 2,000 m | Turf | Hanshin | 1,500 | 4,100 |  |
| 23 | Early December | Chunichi Shimbun Hai | G3 | 2,000 m | Turf | Chukyo | 1,500 | 4,100 |  |
| 23 | Early December | Queen Sho | G3 | 1,800 m | Dirt | Funabashi | 1,000 | ⚠️ | added by `pre_nar`; payout curve `53` absent from `en/race-fans` |
| 23 | Early December | Turquoise Stakes | G3 | 1,600 m | Turf | Nakayama | 1,000 | 3,600 |  |
| 23 | Early December | Stayers Stakes | G2 | 3,600 m | Turf | Nakayama | 2,000 | 6,200 |  |
| 23 | Early December | Champions Cup | G1 | 1,800 m | Dirt | Chukyo | 12,000 | 10,000 |  |
| 24 | Late December | Betelgeuse Stakes | OP | 1,800 m | Dirt | Hanshin | 350 | 2,200 |  |
| 24 | Late December | Galaxy Stakes | OP | 1,400 m | Dirt | Hanshin | 350 | 2,200 |  |
| 24 | Late December | Hanshin Cup | G2 | 1,400 m | Turf | Hanshin | 2,000 | 6,700 |  |
| 24 | Late December | Arima Kinen | G1 | 2,500 m | Turf | Nakayama | 25,000 | 30,000 |  |
| 24 | Late December | Tokyo Daishoten | G1 | 2,000 m | Dirt | Ooi | 12,000 | 8,000 |  |


## G1 index

The 48 graded-tier slots across the three years — Junior 4, Classic 22, Senior 22 — for the common question "when can she run a G1".

> **Races recurring across years appear once per year; do not deduplicate.** **Fourteen** G1 names
> recur in both the Classic and the Senior year, and every one of them recurs at the *same* turn —
> Yasuda Kinen at Turn 11 twice, Takarazuka Kinen at Turn 12 twice, Tenno Sho (Autumn) at Turn 20
> twice, and so on. That is why the index has 48 rows but only 34 distinct G1 names across the
> three years. These are two distinct career slots, not a duplicated row, and collapsing them
> breaks the 48. The `Year` column exists for this reason: it travels with the row, so the
> distinction survives a re-sort or a single-row copy into another document, which a set of year
> subheadings would not.

| Year | Turn | Slot | Race | Tier | Distance | Surface | Track | Fans to enter | Fans for 1st | Notes |
|---|---|---|---|---|---|---|---|---|---|---|
| Junior | 23 | Early December | Asahi Hai Futurity Stakes | G1 | 1,600 m | Turf | Hanshin | 1,000 | 7,000 |  |
| Junior | 23 | Early December | Hanshin Juvenile Fillies | G1 | 1,600 m | Turf | Hanshin | 1,000 | 6,500 |  |
| Junior | 24 | Late December | Hopeful Stakes | G1 | 2,000 m | Turf | Nakayama | 1,000 | 7,000 |  |
| Junior | 24 | Late December | Zen-Nippon Junior Yushun | G1 | 1,600 m | Dirt | Kawasaki | 1,000 | ⚠️ | added by `pre_nar`; payout curve `54` absent from `en/race-fans` |
| Classic | 7 | Early April | Oka Sho | G1 | 1,600 m | Turf | Hanshin | 4,500 | 10,500 |  |
| Classic | 7 | Early April | Satsuki Sho | G1 | 2,000 m | Turf | Nakayama | 4,500 | 11,000 |  |
| Classic | 9 | Early May | NHK Mile Cup | G1 | 1,600 m | Turf | Tokyo | 5,000 | 10,500 |  |
| Classic | 10 | Late May | Japanese Oaks | G1 | 2,400 m | Turf | Tokyo | 6,000 | 11,000 |  |
| Classic | 10 | Late May | Tokyo Yushun (Japanese Derby) | G1 | 2,400 m | Turf | Tokyo | 6,000 | 20,000 |  |
| Classic | 11 | Early June | Yasuda Kinen | G1 | 1,600 m | Turf | Tokyo | 15,000 | 13,000 |  |
| Classic | 12 | Late June | Takarazuka Kinen | G1 | 2,200 m | Turf | Hanshin | 20,000 | 15,000 |  |
| Classic | 13 | Early July | Japan Dirt Derby | G1 | 2,000 m | Dirt | Ooi | 4,000 | 4,500 |  |
| Classic | 18 | Late September | Sprinters Stakes | G1 | 1,200 m | Turf | Nakayama | 15,000 | 13,000 |  |
| Classic | 19 | Early October | M.C. Nambu Hai | G1 | 1,600 m | Dirt | Morioka | 12,000 | 6,000 | added by `pre_nar` |
| Classic | 20 | Late October | Kikuka Sho | G1 | 3,000 m | Turf | Kyoto | 7,500 | 12,000 |  |
| Classic | 20 | Late October | Shuka Sho | G1 | 2,000 m | Turf | Kyoto | 7,500 | 10,000 |  |
| Classic | 20 | Late October | Tenno Sho (Autumn) | G1 | 2,000 m | Turf | Tokyo | 20,000 | 15,000 |  |
| Classic | 21 | Early November | JBC Classic | G1 | 2,000 m | Dirt | Ooi | 12,000 | 8,000 |  |
| Classic | 21 | Early November | JBC Ladies’ Classic | G1 | 1,800 m | Dirt | Ooi | 12,000 | 4,100 |  |
| Classic | 21 | Early November | JBC Sprint | G1 | 1,200 m | Dirt | Ooi | 12,000 | 6,000 |  |
| Classic | 21 | Early November | Queen Elizabeth II Cup | G1 | 2,200 m | Turf | Kyoto | 10,000 | 10,500 |  |
| Classic | 22 | Late November | Japan Cup | G1 | 2,400 m | Turf | Tokyo | 25,000 | 30,000 |  |
| Classic | 22 | Late November | Mile Championship | G1 | 1,600 m | Turf | Kyoto | 15,000 | 11,000 |  |
| Classic | 23 | Early December | Champions Cup | G1 | 1,800 m | Dirt | Chukyo | 12,000 | 10,000 |  |
| Classic | 24 | Late December | Arima Kinen | G1 | 2,500 m | Turf | Nakayama | 25,000 | 30,000 |  |
| Classic | 24 | Late December | Tokyo Daishoten | G1 | 2,000 m | Dirt | Ooi | 12,000 | 8,000 |  |
| Senior | 3 | Early February | Kawasaki Kinen | G1 | 2,100 m | Dirt | Kawasaki | 12,000 | 6,000 | added by `pre_nar` |
| Senior | 4 | Late February | February Stakes | G1 | 1,600 m | Dirt | Tokyo | 12,000 | 10,000 |  |
| Senior | 6 | Late March | Osaka Hai | G1 | 2,000 m | Turf | Hanshin | 20,000 | 13,500 |  |
| Senior | 6 | Late March | Takamatsunomiya Kinen | G1 | 1,200 m | Turf | Chukyo | 15,000 | 13,000 |  |
| Senior | 8 | Late April | Tenno Sho (Spring) | G1 | 3,200 m | Turf | Kyoto | 20,000 | 15,000 |  |
| Senior | 9 | Early May | Kashiwa Kinen | G1 | 1,600 m | Dirt | Funabashi | 12,000 | 8,000 | added by `pre_nar` |
| Senior | 9 | Early May | Victoria Mile | G1 | 1,600 m | Turf | Tokyo | 10,000 | 10,500 |  |
| Senior | 11 | Early June | Yasuda Kinen | G1 | 1,600 m | Turf | Tokyo | 15,000 | 13,000 |  |
| Senior | 12 | Late June | Takarazuka Kinen | G1 | 2,200 m | Turf | Hanshin | 20,000 | 15,000 |  |
| Senior | 12 | Late June | Teio Sho | G1 | 2,000 m | Dirt | Ooi | 12,000 | 6,000 |  |
| Senior | 18 | Late September | Sprinters Stakes | G1 | 1,200 m | Turf | Nakayama | 15,000 | 13,000 |  |
| Senior | 19 | Early October | M.C. Nambu Hai | G1 | 1,600 m | Dirt | Morioka | 12,000 | 6,000 | added by `pre_nar` |
| Senior | 20 | Late October | Tenno Sho (Autumn) | G1 | 2,000 m | Turf | Tokyo | 20,000 | 15,000 |  |
| Senior | 21 | Early November | JBC Classic | G1 | 2,000 m | Dirt | Ooi | 12,000 | 8,000 |  |
| Senior | 21 | Early November | JBC Ladies’ Classic | G1 | 1,800 m | Dirt | Ooi | 12,000 | 4,100 |  |
| Senior | 21 | Early November | JBC Sprint | G1 | 1,200 m | Dirt | Ooi | 12,000 | 6,000 |  |
| Senior | 21 | Early November | Queen Elizabeth II Cup | G1 | 2,200 m | Turf | Kyoto | 10,000 | 10,500 |  |
| Senior | 22 | Late November | Japan Cup | G1 | 2,400 m | Turf | Tokyo | 25,000 | 30,000 |  |
| Senior | 22 | Late November | Mile Championship | G1 | 1,600 m | Turf | Kyoto | 15,000 | 11,000 |  |
| Senior | 23 | Early December | Champions Cup | G1 | 1,800 m | Dirt | Chukyo | 12,000 | 10,000 |  |
| Senior | 24 | Late December | Arima Kinen | G1 | 2,500 m | Turf | Nakayama | 25,000 | 30,000 |  |
| Senior | 24 | Late December | Tokyo Daishoten | G1 | 2,000 m | Dirt | Ooi | 12,000 | 8,000 |  |

## Slot counts

| Year | G1 | G2 | G3 | OP | Pre-OP | Maiden | Debut | total |
|---|---|---|---|---|---|---|---|---|
| Junior | 4 | 2 | 9 | 15 | 26 | 1 | 1 | 58 |
| Classic | 22 | 27 | 47 | 66 | 0 | 0 | 0 | 162 |
| Senior | 22 | 29 | 52 | 83 | 0 | 0 | 0 | 186 |
| Finale | 7 | 0 | 0 | 0 | 0 | 0 | 0 | 7 |

## `[Global]` versus `[JP]` — what actually differs

Four findings, in descending order of how much they matter to an importer.

**1. Exactly two career slots are excluded from `[Global]`.** Prix Niel (Classic, Early September) and Prix Foy (Senior, Early September) carry `unreleased_servers: ['en']` and are the only rows in the whole career schedule flagged off Global. They are also the only two slots at a non-Japanese track in the pool — Longchamp. Everything else in the tables below is present on `[Global]` as far as the export's server flags show.

**2. The eleven other `unreleased_servers: ['en']` race rows are not career races at all.** They have empty `list_ura`, and they are alternate versions of names that *do* appear in the career pool — a second Tenno Sho (Autumn) at Nakayama where the career race runs at **Tokyo**, a 2,200 m Japan Cup at Nakayama where the career race is **2,400 m at Tokyo**, plus a Niigata Sprinters Stakes, the Prix de l'Arc de Triomphe rows, American Oaks and a story-race Hanshin Umamusume Stakes. Reading those eleven as "G1s missing from `[Global]` careers" is wrong: the career Japan Cup, Tenno Sho (Autumn), Sprinters Stakes, Mile Championship and Queen Elizabeth II Cup are all in the tables above and all carry no server flag.

The trap in that pair of rows is the one worth naming: **the career race and the real race are different events.** The career Japan Cup is Tokyo / 2,400 m; the Nakayama / 2,200 m version is the `[JP]`-only row. Anyone filling a distance or track from real-world racing knowledge, or from a `[JP]` guide that uses the real fixture, will write the wrong course for `[Global]`. Take the course from the career slot's own row.

**3. The `[Global]` fan-payout table is identical to `[JP]` wherever both have a row — and it stops short.** `en/race-fans` holds payout curves 1–50; `race-fans` holds 1–61. Every one of the 50 shared curves has byte-identical per-position values, so nothing in the visible fan economy moved between servers, and nothing in the 2026-07-01 rework moved it either, to the resolution this export allows. But **nine career slots ask for curves 51, 52, 53, 54 and 56, which `[Global]` has no row for**: the seven NAR slots (Zen-Nippon Junior Yushun, Sazanka TV Hai ×2, Queen Sho ×2, Diolite Kinen, Tokyo Sprint) and the two Longchamp slots. Those cells are marked ⚠️ in the tables. This is read two ways and the data cannot separate them: GameTora's `[Global]` export has not been extended for content `[Global]` does have, or `[Global]` genuinely does not pay those curves. Until it is resolved, **do not compute a fan projection that depends on those nine slots.**

**4. The career-rank fan ladder is shorter on `[Global]`.** `en/db-files/single_mode_rank` has 98 bands and its top band starts at **71,400** fans; the `[JP]` table has 298 bands and tops out at **190,400**. The two agree band-for-band up to at least band 40, so this is a ceiling difference, not a value difference. It is consistent with the official `[Global]` Open League line ("Only Veteran Umamusume with a Career Rank of A+ or below can enter") against `[JP]`'s 「育成ランク[UC]まで」. The band-id → rank-name mapping is **not** in this export, so no rank label may be attached to a fan figure from this table alone.

### Later-patch slots

23 race rows carry a `did_not_exist` marker — `pre_nar` for the regional-racing set (Kawasaki Kinen, Kashiwa Kinen, M.C. Nambu Hai, Ladies' Prelude, Tokyo Hai, Empress Hai, Kanto Oaks, Diolite Kinen, Sazanka TV Hai, TCK Jo-o Hai, Tokyo Sprint, Sparking Lady Cup, Marine Cup, Queen Sho, Mercury Cup, Cluster Cup, Zen-Nippon Junior Yushun) and `pre_2_5th_anni` for the Longchamp rows. The marker is a **timeline** note about when GameTora's schema gained the row, not a server statement, and none of the `pre_nar` races carries `unreleased_servers`. They are kept in the tables and flagged in Notes. `[Global]` received the scenario that introduced regional racing on 2026-03-12, so presence is the expected reading — but it is the export's silence plus a launch date, not a `[Global]` confirmation, and it sits in Known gaps.

## Scenario differences

| Scenario | Career race calendar | Finale row | What differs |
|---|---|---|---|
| URA Finale | the tables above | URA Finals Qualifier → Semifinal → **Final (URA)** | Nothing at the race layer. `01-ura-finale.md` calls it a "scripted race-goal calendar". |
| Unity Cup | the tables above | … → **Final (Aoharu)** | Same slots, different final row. Team Races are a separate schedule and belong to `06-unity-cup-gametora.md`. |
| Trackblazer | the tables above | … → **Twinkle Star Climax** | Same slots, different final. Progress is measured in Grade Points, not race goals — `03`/`04`/`05`. |
| Our Grand Concert | the tables above | … → **Final (Grand Live)** | ⚠️ Tier B only. See below. |

The brief's assumption that URA Finale and Unity Cup share one calendar is **confirmed by the data**, and the confirmation is structural: the two scenarios have no separate slot rows at all, only separate final rows, and the final fork is the single place the dataset distinguishes them.

**Our Grand Concert stays a boundary.** `docs/scenarios/07-grand-concert.md` records that mechanical extraction for that scenario is **suspended by owner decision (2026-09-27)** pending a primary source. This file does not lift that suspension and invents nothing for the scenario. The one race-level fact the export carries is the `final_live` row named above, plus its `did_not_exist: pre_gl` marker — that is a dataset row, not an extracted rule, and it does not license a Goal list, a finale structure, or any widget for Grand Concert.

## Client corroboration

Four `[Global]` client panels were read directly from the capture corpus and every race cell in them matches the slot the export predicts. The corpus is `docs/game-screenshots/`, captured 2026-07-14 and 2026-07-15, i.e. after the 2026-07-01 rework.

| Capture | Panel | Race cells observed | Export agreement |
|---|---|---|---|
| `Screenshot 2026-07-14 193902.png` | Senior | Osaka Hai Late Mar; Tenno Sho (Spring) Late Apr; **Goal** Tenno Sho (Autumn) Late Oct; Japan Cup Late Nov; **Goal** Arima Kinen Late Dec | 5 / 5 |
| `Screenshot 2026-07-14 193855.png` | Classic | **Goal** NHK Mile Cup Early May; **Goal** Mile Championship Late Nov; **Goal** Arima Kinen Late Dec | 3 / 3 |
| `Screenshot 2026-07-15 162806.png` | Senior | Nikkei Shinshun Hai Early Jan; Kyoto Kinen Early Feb; **Goal** Nikkei Sho Late Mar; **Goal** Tenno Sho (Spring) Late Apr; **Goal** Takarazuka Kinen Late Jun; Kyoto Daishoten Early Oct; **Goal** Arima Kinen Late Dec | 7 / 7 |
| `Screenshot 2026-07-15 162759.png` | Classic | **Goal** Spring Stakes Late Mar; Kyoto Shimbun Hai Early May; **Goal** Tokyo Yushun Late May; Kyoto Daishoten Early Oct; **Goal** Kikuka Sho Late Oct; Queen Elizabeth II Cup Early Nov | 6 / 6 |

**21 of 21 cells agree, with no conflicts.** Two readings of the panel are load-bearing beyond the timing: the label sits *below* its tile, and **Goal** (red banner) is a different state from **Scheduled** (pink badge) — Scheduled is a race the Trainer has entered, Goal is the character's objective. Per `docs/SOURCE-OF-TRUTH.md` §5, a Tier B dataset needs A- or S-tier confirmation before a claim becomes app data; for the calendar spine this file now carries Tier S confirmation.

The remaining 14 supplied captures were read as a batch and not individually re-opened from file; they are the source of the Junior-cell observations (Hakodate Junior Stakes Late Jul, Niigata Junior Stakes Late Aug, Sapporo Junior Stakes Early Sep, Artemis Stakes Late Oct, Asahi Hai Futurity Stakes and Hanshin Juvenile Fillies Early Dec, Hopeful Stakes Late Dec) and of the observation that Junior turns 1–11 render empty. Each of those also matches the export.

## What this unblocks

`docs/adr/0003` Amendment R3 declines to populate `scenario_slots` because "there is no Oka Sho, no fan threshold, no month-and-half placement for any URA target". All three of those now exist with a server qualifier and a source date, so R3's stated reason is spent. **This file does not amend R3** — whether the table is seeded from it or fetched through the engine is an Architect and owner call, and R3's own conclusion (fetch engine, not seeder) is not contradicted here. What changes is which half of R3 is load-bearing: the blocker was the source, and the source now exists.

Two questions are now the owner's, and neither is answered by this file:

- **Seed `scenario_slots` from this file, or fetch it through the engine?** R3's own conclusion was the fetch engine, and nothing here contradicts that; what changed is that the source R3 said was missing now exists.
- **Where does the per-character Goal list live?** `scenario_slots` has no place for it, and the Goal banner is a per-trainee property rather than a scenario property — so the schema question is unchanged by this file and is not a data question.

## Appendix: every flagged race row in the export

All 31 rows in `races.json` carrying either server flag, with whether the row reaches a career slot. This is the complete answer to "which races exist on `[JP]` but not in a `[Global]` career".

| Race | Tier | Distance | Track | In a career slot? | Flag |
|---|---|---|---|---|---|
| Tenno Sho (Autumn) | G1 | 2,000 m | Nakayama | no | `unreleased_servers: ['en']` |
| Mile Championship Nambu Hai | G1 | 1,600 m | Tokyo | no | `unreleased_servers: ['en']` |
| Japan Cup | G1 | 2,200 m | Nakayama | no | `unreleased_servers: ['en']` |
| Sprinters Stakes | G1 | 1,200 m | Niigata | no | `unreleased_servers: ['en']` |
| Mile Championship | G1 | 1,600 m | Hanshin | no | `unreleased_servers: ['en']` |
| Queen Elizabeth Cup | G1 | 2,200 m | Hanshin | no | `unreleased_servers: ['en']` |
| Kawasaki Kinen | G1 | 2,100 m | Kawasaki | yes: 1106 | `did_not_exist: 'pre_nar'` |
| Zen-Nippon Junior Yushun | G1 | 1,600 m | Kawasaki | yes: 1107 | `did_not_exist: 'pre_nar'` |
| Kashiwa Kinen | G1 | 1,600 m | Funabashi | yes: 1108 | `did_not_exist: 'pre_nar'` |
| M.C. Nambu Hai | G1 | 1,600 m | Morioka | yes: 1109,1109_2 | `did_not_exist: 'pre_nar'` |
| Prix de l'Arc de Triomphe | G1 | 2,400 m | Longchamp | no | `did_not_exist: 'pre_2_5th_anni'` |
| Ladies' Prelude | G2 | 1,800 m | Ooi | yes: 1119,1119_2 | `did_not_exist: 'pre_nar'` |
| Tokyo Hai | G2 | 1,200 m | Ooi | yes: 1120,1120_2 | `did_not_exist: 'pre_nar'` |
| Empress Hai | G2 | 2,100 m | Kawasaki | yes: 1121 | `did_not_exist: 'pre_nar'` |
| Kanto Oaks | G2 | 2,100 m | Kawasaki | yes: 1122 | `did_not_exist: 'pre_nar'` |
| Diolite Kinen | G2 | 2,400 m | Funabashi | yes: 1123 | `did_not_exist: 'pre_nar'` |
| Sazanka TV Hai | G2 | 1,800 m | Funabashi | yes: 1124,1124_2 | `did_not_exist: 'pre_nar'` |
| TCK Jo-o Hai | G3 | 1,800 m | Ooi | yes: 1125 | `did_not_exist: 'pre_nar'` |
| Tokyo Sprint | G3 | 1,200 m | Ooi | yes: 1126 | `did_not_exist: 'pre_nar'` |
| Sparking Lady Cup | G3 | 1,600 m | Kawasaki | yes: 1127,1127_2 | `did_not_exist: 'pre_nar'` |
| Marine Cup | G3 | 1,600 m | Funabashi | yes: 1128,1128_2 | `did_not_exist: 'pre_nar'` |
| Queen Sho | G3 | 1,800 m | Funabashi | yes: 1129,1129_2 | `did_not_exist: 'pre_nar'` |
| Mercury Cup | G3 | 2,000 m | Morioka | yes: 1130,1130_2 | `did_not_exist: 'pre_nar'` |
| Cluster Cup | G3 | 1,200 m | Morioka | yes: 1131,1131_2 | `did_not_exist: 'pre_nar'` |
| Prix Niel | G2 | 2,400 m | Longchamp | yes: 2905 | `unreleased_servers: ['en']` |
| Prix Foy | G2 | 2,400 m | Longchamp | yes: 2907 | `unreleased_servers: ['en']` |
| Prix de l'Arc de Triomphe | G1 | 2,400 m | Longchamp | no | `unreleased_servers: ['en']` |
| Prix de l'Arc de Triomphe | G1 | 2,400 m | Longchamp | no | `unreleased_servers: ['en']` |
| Prix de l'Arc de Triomphe | G1 | 2,400 m | Longchamp | no | `unreleased_servers: ['en']` |
| American Oaks | G1 | 2,000 m | Santa Anita Park | no | `unreleased_servers: ['en']` |
| Hanshin Umamusume Stakes (Story Race Use) | G2 | 1,400 m | Hanshin | no | `unreleased_servers: ['en']` |

## Provenance

| Fact class | Source | Tier | URL |
|---|---|---|---|
| Slot timing, tier, distance, surface, track, entry gate | GameTora `race_instances` | B | [race_instances.294424fc.json](https://gametora.com/data/umamusume/race_instances.294424fc.json) |
| Slot timing, tier, entry gate (independent second copy of the same rows) | GameTora `ura-races` | B | [ura-races.c12e8867.json](https://gametora.com/data/umamusume/ura-races.c12e8867.json) |
| Server flags, `list_ura`, client `name_en` | GameTora `races` | B | [races.55dde7c9.json](https://gametora.com/data/umamusume/races.55dde7c9.json) |
| Track display names | GameTora `racetracks_extended` | B | [racetracks_extended.effe0119.json](https://gametora.com/data/umamusume/racetracks_extended.effe0119.json) |
| `[Global]` fan payouts | GameTora `en/race-fans` | B | [en/race-fans.ea0816c3.json](https://gametora.com/data/umamusume/en/race-fans.ea0816c3.json) |
| `[JP]` fan payouts, for the 1–50 identity test | GameTora `race-fans` | B | [race-fans.82ab7152.json](https://gametora.com/data/umamusume/race-fans.82ab7152.json) |
| Scenario `start_en`, for the `[JP-Only]` ruling | GameTora `scenarios` | B | [scenarios.61b7c51c.json](https://gametora.com/data/umamusume/scenarios.61b7c51c.json) |
| Career-rank fan ladders | GameTora `en/` and `[JP]` `db-files/single_mode_rank` | B | [en 98 bands](https://gametora.com/data/umamusume/en/db-files/single_mode_rank.d624caeb.json) · [JP 298 bands](https://gametora.com/data/umamusume/db-files/single_mode_rank.aa219d9e.json) |
| Tier-label distribution test | uma.guide Trackblazer guide, page updated 2026-04-29, transcribed at `docs/scenarios/04` | A | https://uma.guide/guides/trackblazer |
| Goal vs Scheduled states, month-half grid, 21 race cells | `[Global]` client captures, `docs/game-screenshots/` | S | local corpus, 2026-07-14 / 2026-07-15 |

Snapshot paths: `research-scratch/data/json/race_instances.json` and siblings, with `research-scratch/data/manifest.json` as the 2026-09-27 baseline. That directory is gitignored, so the URLs above are the durable citation and the snapshot is the local working copy.

**To reproduce:** resolve every key through [the live manifest](https://gametora.com/data/manifests/umamusume.json) rather than hardcoding a hash, and send a browser `User-Agent` with `Accept: application/json` — the data endpoint returns 403 to a plain client, which reads as a missing dataset and is not one. Join `race_instances[].instance` → `races[].id` for names and flags, `races[].track` → `racetracks_extended[].id` for track names, and `race_instances[].fans_gain` → `en/race-fans[].id` then `[].fans[].order == 1` for the first-place payout. Turn number is `(month − 1) × 2 + half`. The generator used for the tables is `research-scratch/gen_calendar.py`, output in `research-scratch/calendar-tables.md`.

## Known gaps

- ⚠️ **Nine slots have no `[Global]` payout curve** (curves 51–54, 56). Marked in the tables; do not project fans through them.
- ⚠️ **`did_not_exist` is a timeline marker, not a server marker.** The 17 `pre_nar` career races are in these tables because nothing flags them off `[Global]`, not because a `[Global]` source names them. One Tier A or S confirmation of regional racing in a `[Global]` career would settle it.
- ❌ **Per-character Goal races are not in the export.** The client renders them; no GameTora key carries them. `characters_extended.json` is a name list and `character_profiles.json` is flavour text. Building a Goal list means either per-character guide transcription or client capture, and it is the missing input for any "next objective" widget.
- ❌ **Whether the uma.guide distribution table is a narrower universe or simply an older sub-table.** The 12-cell match is exact on the base pool and breaks when later additions are included; which explanation holds is not established, and the two readings imply different things about `[Global]` race counts.
- ❌ **`direction`, `course`, `season` and `group` are undecoded.** The export stores `direction` 1/2/4, `course` 1/2/3, `season` 0–5, `group` always 1. The official race line format ("Kyoto / Turf / 2,200m (Medium) / Right-Handed / Outer / Autumn / Sunny / Firm") implies handedness and inner/outer live in two of those fields, but no mapping is asserted here, so no table column uses them.
- ❌ **Trackblazer's own race availability.** `03`/`04`/`05` describe Grade Points, rivals and fatigue; none states whether Trackblazer's picker offers the same 404 monthly slots as the tables above or a superset. This file's tables are the shared career schedule and are not claimed to be Trackblazer's full list.
- ❌ **Grand Concert.** Unchanged from `07`: extraction suspended by owner decision, and this file adds only the `final_live` dataset row.

Compiled 2026-09-28. Tables are generated from the export, not typed; every unverified item above is marked, and no race in the tables is inferred from a `[JP]` source.
