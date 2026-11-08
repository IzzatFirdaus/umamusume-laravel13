> **Alternate-door walk.** This document records a walk of the Rice Shower / Unity Cup snapshot taken through the **snapshot import** (`/career/snapshot`, door 2 of 3). It is preserved as the alternate-door evidence for the position-carrying question, not as the canonical walk 7. The canonical walk 7 — the wizard door, which is the primary path a new user takes and the door walk 6 also used — is at `rice-shower-unity-cup-ux-walk-7.md`. The two walks are complementary: the snapshot-door walk establishes that the application **can** carry a Senior Early October position; the wizard-door walk establishes whether the wizard **also** can, via the training form's `Turn` field, or whether the wizard is genuinely new-career-only.

# Rice Shower / Unity Cup — UX walk 7, snapshot door (`RS-UC-2026-11`)

Status: **walk complete.** All sections filled.
The data sheet in §2 was filled **before the first click** (see §2.0 timestamp); the walk
consults only that sheet once started.

## 7.1 Header

- **Audit name / ID:** Rice Shower Unity Cup UX walk 7, snapshot door / `RS-UC-2026-11` (alternate door; the canonical wizard-door walk 7 is `RS-UC-2026-12`).
- **Source document:** `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` (read end to end once,
  pre-walk; clean at commit `9c38a019`, 2026-11-06 23:46:46 +0800).
- **Secondary reference (context, not evidence):** `docs/UMAMUSUME_REFERENCE.md`.
- **Starting URL:** `http://127.0.0.1:8000/` (HTTP 200, 0.31 s; server already running, not
  started by this walk).
- **Walk start:** 2026-11-07 10:48:00 +0800 (Asia/Kuala_Lumpur). **Walk end:** 2026-11-07 11:54:00 +0800 (Asia/Kuala_Lumpur). Duration ~1h 06m.
- **Browser / environment:** Chrome 157.0.0.0 (UA `Mozilla/5.0 (Windows NT 10.0; Win64; x64)
  AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36`), Windows 10.0.26300
  x64, viewport 1920×889 CSS px (devicePixelRatio 1; outer window 1920×1032), driven by the
  Playwright MCP. **Devtools: not opened.** Read-only `page.evaluate` used for environment
  facts and DOM reads; no DOM writes, no request replay, no state mutation.
- **Pre-existing client state (observed, not created):** one cookie `XSRF-TOKEN`;
  localStorage keys `phpdebugbar-open=0`, `phpdebugbar-height=40`, `tema=ikut-sistem`,
  `trainer-desk.nav-rail=compact`; no `data-theme` attribute and empty `<html>` class (light
  render); no debugbar element in the DOM; sessionStorage empty. Theme left as found.
- **Git commit / application version:** `105765357de38ea92de06561fe808d2976422cc5` (master).
  Working tree carries four peer-owned dirty paths not touched by this walk:
  `D docs/audits/AUDIT-SUPPORT-CARDS-UIUX-2026-11-05.md`, `D docs/proposals/PLAN_SLICES_6_7.md`,
  `M docs/research-scratch/AUDIT-AND-VERIFICATION.md`, `M docs/research-scratch/PROCESS-PLANS.md`.
- **Run identity block (§2.1 of the brief):** trainee **Rice Shower**, card **`[Rosy Dreams]`**,
  rarity ★3, Potential Lvl 2; scenario **Unity Cup** (full client label "Unity Cup: Shine On,
  Team Spirit!"); snapshot point **Senior Year Early Oct**, turn **67** of 72, **5 turns
  remaining**; lifecycle **mid-career, in progress**; **no run number in the source**.
- **No-seeding statement:** the walk was performed entirely through the browser UI. No
  seeder, factory, direct DB insert/update/delete, SQL, tinker/REPL, state-mutating artisan
  command, API substitution, devtools DOM edit, or request replay was used to enter data.
- **Primary-pass scope / stopping point:** All nine career-nav screens visited in forward order; snapshot import completed; no turn recorded, no event recorded, no inheritance recorded, no deck saved — all write paths demonstrated at empty state per the source's snapshot-only scope.
- **Door and turn index used:** Door 2 ("Import an existing career" → `/career/snapshot`). Position entered as Senior Year / Month 10 / Early; application derives turn 67 of 72. Next-record convention on decision screens = 68. Races screen shows incorrect "Undo turn 18". See §7.5.
- **Coverage statement:** the client's frames cover the **in-career** screens (`Career`,
  `Sparks`, `Perks`, `Learn`, `Log`, the in-career rail) and the five `Career Profile` panels,
  plus the Senior-year endgame (race card, training tile, Team Info, roster). The **pre-career
  setup flow and turn 0 are unfilmed**, as are the preseason rounds' own screens and the
  finals' race cards. Consequently **every setup-path claim in this audit is
  source-transcribed or publisher-derived, not frame-observed**; where the current document
  and a frame disagree, the frame wins and the disagreement is logged as an open item (§2.4).
- **Contamination statement:** read as **context only, never as UI evidence**: the source
  document's tool-internal citations (`support_cards` rows 30003/30016/30018/30019/30020/30030,
  `SupportCard::isScenarioLink('unity_cup')`, `config('scenarios.php')`,
  `CareerTraineeSelectTest`, `database/seeders/data/gametora-characters.e9e9ee6d.json`, the deck
  table's catalog / "Client rows visible" columns, and the "D8 of the UX walk remediation" /
  `AUDIT-AND-VERIFICATION.md` references); and this repository's own prior trail (walks 1–6,
  the round 2–6 registers, `ui-ux-application-context-brief.md`, `flows-*.md`,
  `rice-shower-unity-cup-ux-walk-resolutions.md`, `KNOWN-ISSUES.md`, `docs/framework-ui-ux/`,
  and the peer-landed `rice-shower-unity-cup-round6-reconciliation.md` /
  `rice-shower-unity-cup-round6-slice-audits.md`). Where a prior finding reproduces, this audit
  gives its own observation; where it does not, that is recorded as a finding in its own right.

## 2.0 Data-sheet provenance

Filled 2026-11-07, before the first click (first click occurs at S02). Anchors cite the
**current** section title and line in `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md`, re-verified
against titles after the end-to-end read (the brief's §-numbers are pre-revision). Values the
brief's sheet omitted but the current document carries are added and marked **[added]**.
Disagreements between the brief's sheet and the current document are logged in §2.4, not
resolved here.

## 2.3 Pre-extracted data sheet

| Datum | Value to enter | Source (title @ line) |
| --- | --- | --- |
| Trainee name | Rice Shower | §1.1 Run identity and moment @47 |
| Card / epithet | `[Rosy Dreams]` | §1.1 @47 |
| Rarity | ★3 | §1.1 @47 |
| Potential / awakening | Potential Lvl 2 | §1.1 @47 |
| Scenario | Unity Cup (full: "Unity Cup: Shine On, Team Spirit!") | §1.1 @47 |
| Snapshot point | Senior Year Early Oct | §1.1 @47 |
| Turn index | 67 | derived, §2.5 turn-mapping; §1.1 @47 |
| Turns remaining | 5 | §1.1 @47 |
| Lifecycle status | mid-career, in progress | §1.1 @47; §3 @835; §5 @868 |
| Run number | *(not present in source)* | — |
| Speed | 607 | §1.2 Live stat line and the caps above it @54 |
| Stamina | 577 | §1.2 @54 |
| Power | 549 | §1.2 @54 |
| Guts | 736 | §1.2 @54 |
| Wit | 397 | §1.2 @54 |
| Speed cap (in force) | 1325 | §1.2 @54 |
| Stamina cap (in force) | 1322 | §1.2 @54 |
| Power cap (in force) | 1368 | §1.2 @54 |
| Guts cap (in force) | 1308 | §1.2 @54 |
| Wit cap (in force) | 1800 | §1.2 @54 |
| Scenario floor (S/St/P/G/W) | 1300/1300/1300/1300/1800 | §1.2 @54 |
| Over-floor delta | +25/+22/+68/+8/N/A | §1.2 @54 |
| Growth (S/St/P/G/W) | +0 / +10 / +0 / +20 / +0 | §1.2 @54 |
| Unspent SP | 173 | §1.2 @54 |
| Stat grades (S/St/P/G/W) | B / C+ / C+ / B+ / D+ | §1.2 @54 |
| Mood | GREAT | §1.7 Mood, Energy, and the last observed training turn @247 |
| Energy | *(low; bar near a third; no number)* | §1.7 @247 |
| Energy warning string | "Your trainee's energy is getting low, so she's more susceptible to injury. Rest is important too." | §1.7 @247 |
| Trainee bubble (doc reading) | "Phew... I guess I'm just... a little tired..." | §1.7 @247 |
| Trainee bubble (brief §2.3e reading) | "...I'm okay. I'm not tired... or anything... I'm just fine..." | brief §2.3e; both kept, §2.4 |
| Fans **[added]** | 209,245 | §1.1 @47; §1.15 Career Profile panels @468 |
| Class **[added]** | Star; 30,755 to Top Star (240,000) | §1.15 @468 |
| Aptitudes (client, frame-corrected) | Turf A, Dirt C; Sprint E, Mile B, Medium A, Long A; Front A, Pace A, Late B, End C | §1.2 @54; §11 C-01/C-02 @1611-1612 |
| Deck card 1 | Tokai Teio `[Dream Big!]`, Speed, 35/35 | §1.4 Deck, all six cards, all SSR @111 |
| Deck card 2 | Nishino Flower `[Even the Littlest Bud]`, Speed, 30/30 | §1.4 @111 |
| Deck card 3 | Biko Pegasus `[Double Carrot Punch!]`, Speed, 30/30 | §1.4 @111 |
| Deck card 4 | Super Creek `[Piece of Mind]`, Stamina, 50/50, **borrowed** (Friends pill) | §1.4 @111; frame `85e4c75a` |
| Deck card 5 | Matikanetannhauser `[Just Keep Going]`, Guts, 30/30 | §1.4 @111 |
| Deck card 6 | Haru Urara `[Urara's Day Off!]`, Guts, 35/35 | §1.4 @111 |
| Skills owned (visible ten) | Blue Rose Closer Lvl 3, Red Shift/LP1211-M, Corner Adept ○, Swinging Maestro, Nimble Navigator, Steadfast, Deep Breaths, Extra Tank, Pace Chaser Straightaways ○, Shrewd Step | §1.3 Skills owned @103; §11 C-04 @1614 |
| Skill owned (not in visible ten) | Corner Recovery ○ (Obtained on Learn) | §1.3 @103; frame `09e754d6` |
| Ancestor 1 (legacy) | Vodka `[Wild Top Gear]`, rank B+ | §1.5 Inheritance, from the Sparks panels @173 |
| Ancestor 2 (legacy) | Maruzensky `[Formula R]`, rank UG, **Guest** | §1.5 @173 |
| Grandparents | Daiwa Scarlet `[Peak Blue]` (B+ Vodka side; **S+** Maruzensky side per §11 C-03), Seiun Sky `[Reeling in the Big One]` (A), Taiki Shuttle `[Wild Frontier]` (UG) | §1.5 @173; §11 C-03 @1613 |
| Blue sparks | Stamina ★★★ (Seiun Sky), Power ★★★ (Taiki Shuttle), Speed ★★☆ (Maruzensky), Speed ★★☆ (Daiwa L2), Guts ★☆☆ (Daiwa L1, doc) / ★★☆ (brief), Power ★☆☆ (Vodka, doc) / ★★☆ (brief) | §1.5 @173; §2.4 |
| Team name / motto **[added]** | Blue Bloom / "Dreaming Big" | §1.8 Team Info panel @268 |
| Team rank | S (five stars) | §1.8 @268 |
| League placement | 8th | §1.8 @268 |
| Ranking-bonus tooltip | "Team Ranking Bonus / All Attributes + 30" (bound to league 8th) | §1.8 @268; §7 finding 11 |
| Unity Trainings | 55 | §1.8 @268 |
| Spirit Bursts | 6 | §1.8 @268 |
| Extreme Spirit Bursts | 5 | §1.8 @268 |
| Team members | 20/20 | §1.8 @268 |
| Team stat grades | Speed A, Stamina A, Power A, Guts S, Wit A | §1.8 @268 |
| Facility levels | Speed 4, Stamina 4, Power 4, Guts 5, Wit 4 | §1.7 @247; §2.6 @754 |
| Roster **[added]** | 17 named guests + trainee = 18; two clipped | §1.9 The roster @313 |
| Races won | 4 preseason rounds (R1–R4) | §1.6 Unity Cup state @209 |
| Race scheduled this turn | Kyoto Daishoten (G2, Kyoto Turf 2400m Medium, Right/Outer, Fall, Soft); 6,700 fans on win; Full Gate 18; entry "2,000 fans or more" met | §1.6 @209 |
| Race-day alternative **[added]** | Mile Championship Nambu Hai (G1, Morioka Dirt 1600m Mile, Left); 6,000 fans | §1.6 @209 |
| Training tile **[added]** | Speed Lv4, banner "Floor Cleaning", preview +18 Speed / +8 Power / +6 Skill Points, Failure 39% | §1.7 @247 |
| Pending race | Unity Cup finals, after Senior Late Dec, `5 turn(s)`, NEXT | §1.6 @209 |
| Active objective | Place 1st in Arima Kinen, Senior Late Dec, turn 72, `Entry criteria met!`, `In progress`, `Turns left: 5` | §1.6 @209; §1.15 @468 |
| Cleared objectives | Nikkei Sho top 3 (2,000+); Tenno Sho (Spring) 1st (20,000+); Takarazuka Kinen top 3 (20,000+); all `CLEAR!` | §1.6 @209; §1.15 @468 |

## 2.3b Per-ancestor spark chains (frame-verified, trainer-specified)

Enter per ancestor **instance**, not per card. No Wit spark anywhere.

- **Vodka `[Wild Top Gear]` B+** (1st Legacy): Power ★★☆ → Late Surger ★★☆ → Cut and Drive! ★★☆ → white: Oka Sho ★☆☆, Victoria Mile ★☆☆, Shuka Sho ★★☆, Japan C. ★★☆, Extra Tank ★★☆, Soft Step ★★★.
- **Daiwa Scarlet `[Peak Blue]` B+** (Vodka-side parent): Guts ★★☆ → Mile ★☆☆ → Resplendent Red Ace ★★☆ → white: Osaka Hai ★★☆, Tenno Sho (Autumn) ★☆☆, Competitive Spirit ○ ★★★, Pace Chaser Straightaways ○ ★★☆.
- **Seiun Sky `[Reeling in the Big One]` A** (Vodka-side parent): Stamina ★★★ → Turf ★★☆ → Angling and Scheming ★★☆ → white: Kikuka Sho ★☆☆, Corner Recovery ○ ★★☆, URA Finale ★☆☆.
- **Maruzensky `[Formula R]` UG, Guest** (2nd Legacy): Speed ★★☆ → Turf ★★☆ → Red Shift/LP1211-M ★☆☆ → white: Takarazuka Kinen ★★☆, Asahi Hai F.S. ★★☆, Corner Adept ○ ★★☆, Ramp Up ★★☆, Mile Straightaways ○ ★☆☆, Ignited Spirit: Guts + ★☆☆.
- **Daiwa Scarlet `[Peak Blue]` S+** (Maruzensky-side parent): Speed ★★☆ → Front Runner ★★☆ → Resplendent Red Ace ★★☆ → white: Osaka Hai ★★☆, Tenno Sho (Spring) ★★☆, NHK Mile C. ★★☆, Japan C. ★★☆, Corner Adept ○ ★★☆, Unyielding Spirit ★★☆, Front Runner Savvy ○ ★☆☆.
- **Taiki Shuttle `[Wild Frontier]` UG** (Maruzensky-side parent): Power ★★★ → Pace Chaser ★★☆ → Shooting for Victory! ★★☆ → white: NHK Mile C. ★★☆, Sprinters S. ★☆☆, JBC L. Classic ★★☆, Tokyo Daishoten ★★☆, Corner Adept ○ ★★☆, Corner Acceleration ○ ★★☆, Productive Plan ★★☆, Shifting Gears ★☆☆, Pace Chaser Straightaways ○ ★★★, Disorient ★★☆, Sympathy ★★☆, Head-On ★★☆, Glittering Star ★★☆.

Star bounds by kind: blue/white to ★★★, pink/green to ★★. Rank badges per instance:
B+, B+, A, UG, S+, UG. Guest flag on Maruzensky only.

## 2.3c The Perks panel (frame-verified)

Tabs `Support Effects` / `Skills`. Skills tab groups `Trainee Skills` (no chip) and
`Card Skills` (source portraits); a name may appear in both; one row carries **two** portraits.
Eleven rows: **Updrafters (two portraits)**, Steadfast, Deep Breaths, Hesitant Pace Chasers,
Meticulous Measures, Stop Right There!, Thunderbolt Step, Soft Step, Pace Chaser Corners ○,
Shrewd Step, Fighter. `○` corner variant present. Icon tiles: orange running/positioning,
blue recovery/endurance, green condition/venue.

## 2.3d The Learn screen and the in-career rail (frame-verified)

Header `Learn` + `i`; trainee portrait, `Full Stats`, `Skill Points 173`. Rows: coloured tile,
name with `○`, effect text with style/distance in parentheses, orange hint badge, `-`/price/`+`.
Owned rows greyed with pink **`Obtained`** replacing price and controls. `Confirm` / `Reset`;
bottom `Back`, `Skip ▶▶`, `Quick`, menu. Hint ladder Lv1 10% / Lv2 20% / Lv3 30% / Lv4 35% /
Lv Max 40%; printed price is discounted and **floored**.

33-row price fixture (name, hint, printed price): Ignited Spirit SPD Lv1 180; STA Lv3 140;
PWR Lv1 180; GUTS Lv4 130; Straight Descent none 120; Highlander none 160; Front Runner
Corners ○ Lv3 91; Pace Chaser Straightaways ○ Lv4 91; Pace Chaser Corners ○ Lv4 84; Plan X
Lv1 272; Countermeasure Lv2 128; Soft Step LvMax 96; Subdued Front Runners Lv1 117; Subdued
Pace Chasers Lv1 130; Hesitant Pace Chasers Lv2 104; Outer Swell Lv1 162; Gap Closer Lv1 144;
Productive Plan Lv3 112; Calm in a Crowd none 170; Ramp Up none 170; Straightaway Acceleration
none 170; It's On! Lv3 289; Unruffled Lv4 280; Rushing Gale! Lv1 323; Long Shot ○ Lv1 81;
Firm Conditions ○ Lv1 81; Sunny Days ○ Lv1 81; Inner Post Proficiency ○ Lv1 81; Kyoto
Racecourse ○ Lv1 81; Hakodate Racecourse ○ Lv2 56; Cut and Drive! Lv2 160; Resplendent Red
Ace Lv1 180.

Obtained list: Steadfast, Deep Breaths, Extra Tank, Shrewd Step, Nimble Navigator, Corner
Recovery ○, Corner Adept ○, Swinging Maestro, Blue Rose Closer, Red Shift/LP1211-M.
Tints: plain; olive-gold on It's On!/Unruffled/Rushing Gale!/Swinging Maestro; green-to-pink
rainbow on Blue Rose Closer; unexplained, travels with the skill. Shop sells ancestry skills
(Cut and Drive!, Resplendent Red Ace).

Log: **`There is no log.`** Rail: **Jukebox, Sparks, Log, Career Profile, Agenda, Item
Request, Menu**. PC dual-pane; Steam overlay in one frame.

## 2.3e The in-career Career screen (frame-verified)

Header `Career` + `i`; calendar tile **`5 turn(s) left`** with blue tab **`Senior Year Early
Oct`**; goal bar **`Place 1st in Arima Kinen`** + **`Entry criteria met!`** (orange) +
`Details`; second badge **`5 Turn(s) Until the Unity Cup`**; `Energy` bar; pink mood chip
**`↑ GREAT`**; laurel `Blue Bloom` + large `S` + five stars + `Dreaming Big`; Tazuna `HINT`
bubble; trainee bubble; class badge `Star` + `i`; stat strip grade letter + in-force cap
(`B` 607/1325 …) + `Skill Pts 173`; buttons `Rest`, `Training`, `Skills`, `Infirmary`,
`Recreation`, `Races` with pink `Scheduled Race` tag; bottom `Skip ▶▶`, `Quick`, menu.
Three distinct counters: goal countdown, Unity Cup countdown, `Entry criteria met!`.

## 2.3f Career Profile panels (frame-verified)

- **Featured Cards** (Nishino Flower SSR): unexplained blue hat icon; `Lv 30 / 30`;
  **`0 SP to next level`**; `Unique Perk` "Even the Littlest Bud" Lv 30 (Mood Effect and
  Initial Friendship Gauge); **`Unity Cup Exclusive`** badge; laurel grade `B`; stats
  Speed A 806/1080, Stamina C+ 591/860, Power B 680/960, Guts B 625/880, Wit B 610/850;
  aptitudes Turf A / Dirt F; Sprint A / Mile A / Medium D / Long C; Front F / Pace A /
  Late A / End C; **four tabs `Support Effects`, `Skills`, `Career Events`, `Flavor Text`**;
  Support Effects lists Friendship Bonus 15%, Mood Effect 20%, Training Effectiveness 5%.
- **Unity Cup Available**: header laurel `S`, `Blue Bloom`, `Dreaming Big`, `8th`; one row per
  round — R1 `Held` / After Junior Year Late Dec / `WIN`; R2 Classic Year Late Jun; R3 Classic
  Year Late Dec; R4 Senior Year Late Jun; `Unity Cup Finals` with `NEXT`, `Held`, After Senior
  Year Late Dec, `5 turn(s)`.
- **Mood Effect panel**: GREAT +20% training / +4% running; GOOD +10/+2; NORMAL 0/0;
  BAD −10/−2; AWFUL −20/−4; `Current ->` marker.

## 2.3g Class ladder, goals and trainee details (frame-verified)

- **Class ladder (nine tiers):** Legend 320,000 / Top Star 240,000 / **Star 160,000
  (current)** / Platinum 100,000 / Gold 50,000 / Silver 20,000 / Bronze 5,000 / Beginner
  1st place / Debut-Maiden base; `←Current` marker; `Fans 209,245` beneath.
- **Goals:** cleared — Nikkei Sho top 3 (Senior Late Mar, `2,000 or above`, `CLEAR!`);
  Tenno Sho (Spring) 1st (Senior Late Apr, `20,000 or above`, `CLEAR!`); Takarazuka Kinen
  top 3 (Senior Late Jun, `20,000 or above`, `CLEAR!`). Active — Place 1st in Arima Kinen
  (Senior Late Dec, `Entry criteria met!`, `In progress`, `Turns left: 5`).
- **Umamusume Details:** card art, three stars, potential level, stats, aptitudes (client
  letters Mile B / Front A / Late B / Dirt C / End C), growth rate, tabs, ten chips; the
  ten-skill list lives here (`Skills` tab), **not** in Perks (§11 C-04).

## 2.4 Sheet-vs-document disagreements (recorded, not resolved)

1. **Dirt / End aptitude:** brief sheet `Dirt G` / `End G`; current doc §11 C-01/C-02 corrects
   to `Dirt C` / `End C` from frame `1dde6b2f`. Frame wins → enter **C / C**.
2. **Vodka Power spark:** brief/in-career ★★☆; doc post-career ★☆☆. Already open in doc §6
   item 10 / §11. Enter the in-career reading ★★☆; keep ★☆☆ open.
3. **Daiwa L1 Guts and Resplendent Red Ace:** brief ★★☆; doc §1.5 ★☆☆ on both branches.
   **Unflagged in the doc** — an additional open item. Enter brief ★★☆; keep doc ★☆☆ open.
4. **Fans / class:** absent from the brief's sheet; present in doc §1.1/§1.15. Added as
   **[added]** rows, not treated as a conflict.
5. **Perks 11-row list and Learn Obtained enumeration:** exist only in the brief (§2.3c/d),
   not enumerated in doc §1.12/§1.13. Treated as brief-supplied frame transcription.
6. **Trainee bubble:** two transcriptions (doc §1.7 vs brief §2.3e). Both kept in the sheet.
7. **Maruzensky rank `UG` vs trainer's `UG1`:** the brief asks to confirm from a
   higher-resolution crop; **no frame image exists in this tree** (`Glob **/*24f5b5d0*` →
   none), so the confirmation cannot be performed. Enter the frame reading `UG`; keep `UG1`
   open and record the inability.

## 7.2 Screen ledger

| Screen ID | Route | Arrived from | Exited via |
| --- | --- | --- | --- |
| S01 | `/` | entry (browser_navigate) | "Import an existing career" link → S02 |
| S02 | `/career/snapshot` | S01 link | "Enter the snapshot" link → S03 |
| S03 | `/career/snapshot/setup` | S02 link | "Review snapshot" → S04 |
| S04 | `/career/snapshot/review` | S03 submit | "Record snapshot" (after understand-checkbox) → S05 |
| S05 | `/training-runs/2/cockpit` | S04 commit | "Training" career-nav link → S06 |
| S06 | `/training-runs/2/training` | S05 nav link | "Races" career-nav link → S07 |
| S07 | `/training-runs/2/races` | S06 nav link | "Skills" career-nav link → S08 |
| S08 | `/training-runs/2/skills` | S07 nav link | "Events" career-nav link → S09 |
|| S09 | `/training-runs/2/events` | S08 nav link | "Inheritance" career-nav link → S10 ||
|| S10 | `/training-runs/2/inheritance` | S09 nav link | "Deck" career-nav link → S11 ||
|| S11 | `/training-runs/2/deck` | S10 nav link | "Timeline" career-nav link → S12 ||
|| S12 | `/training-runs/2/timeline` | S11 nav link | "Result" career-nav link → S13 ||
|| S13 | `/training-runs/2/result` | S12 nav link | *(walk end)* ||

## 7.3 Screen-by-screen walk

### S01 — Dashboard (`/`)

**Route and trigger.** Entry point `http://127.0.0.1:8000/`, reached by direct navigation
(before any click). Title `Dashboard`; single `h1` "Trainer Desk".

**What the screen asks for.** Nothing is required; it is a hub. It offers the three creation
doors and read-only counts. Observed copy: "0 active careers, 0 finished runs, and 4 permanent
Global scenarios."; "Not recorded: no career is running."; "Start a career to track turns,
Energy and race record in one place, or import one you have already started in the client.";
"A running career shows the trainee, the turn, Energy, Mood and Skill Points."

**The three doors (identified here, chosen at S02):**
1. **New career / Start a career** → `/career/setup/scenario` (wizard). Present twice (header
   "New career", nav "New Career", and the primary "Start a career" button).
2. **Import an existing career** → `/career/snapshot` (snapshot import).
3. **Import a historical career** → `/training-runs/import` (file import), under Quick actions.

**Values entered + source trace.** None entered on this screen (no fields).

**Source data with no UI home.** None became relevant here beyond the doors; the run's
position (Senior Year Early Oct) matches door 2's vocabulary, to be confirmed at S02.

**Friction / errors / dead ends.** None observed. Console: one entry, the Laravel Boost
browser-logger notice (`🔍 Browser logger active (MCP server detected)…`); no errors.

**Read-only counts observed (context, not run data):** Careers running 0; Trainees catalogued
133; Skills 1,910; Support cards 559; Veterans 0 banked; Review queue 304 pending matches;
Data status "Global data · Current", "Verified 2026-11-02 · GameTora scenarios.json".

**Outcome / next screen.** Clicked "Import an existing career" → S02 `/career/snapshot`.
Screenshot: `docs/audits/RS-UC-2026-11-snapshot-door/screens/S01-dashboard.png` (visually reviewed: light
theme, compact icon rail, three doors visible).

### S02 — Snapshot entry (`/career/snapshot`)

**Route and trigger.** From S01 "Import an existing career". Title "Start from existing
snapshot"; `h1` "Start from existing snapshot". Stepper: Entry (current), Setup (link),
Review (disabled button).

**What the screen asks for.** Nothing yet; it is the door's landing. Copy: "Enter at least one
record to continue." and "Record a career that is already underway, exactly as far along as
your client shows it. **Nothing pretends it is turn 1: the position you enter becomes the
run's own position, and every surface that counts from there counts from that point.**" and
"Enter what you can read and leave what you cannot. A figure you have not read is recorded as
absent rather than guessed at, so the tool never states a number you did not give it."

**Values entered + source trace.** None (no fields). This screen's vocabulary ("already
underway", "exactly as far along as your client shows it", "not turn 1") is the match for the
source's "Senior Year Early Oct" mid-career snapshot; it is the door chosen (§7.9).

**Source data with no UI home.** None became relevant here.

**Friction / errors / dead ends.** None observed. The disabled "Review" stepper button is
correct gating, not a dead end.

**Outcome / next screen.** Clicked "Enter the snapshot" → S03 `/career/snapshot/setup`.
Screenshot: `docs/audits/RS-UC-2026-11-snapshot-door/screens/S02-snapshot-entry.png`.

### S03 — Snapshot setup (`/career/snapshot/setup`)

**Route and trigger.** From S02 "Enter the snapshot". Title "Record an existing career";
`h1` same. Stepper 1 Entry / 2 Setup (current) / 3 Review. Header copy: "Enter what your
client shows right now. The position becomes the run's own position, and a field you leave
empty is recorded as not provided rather than guessed at. You will read the snapshot back
before it is written."

**What the screen asks for.**
- `Scenario *` select: Choose / URA Finale / Unity Cup / Trackblazer / Our Grand Concert.
- `Trainee *` select: 133 names (includes Rice Shower).
- **Career position:** `Year *` (Junior/Classic/Senior), `Month *` (1–12), `Half *`
  (Early/Late). Copy: "Where the career stands, as the client spells it. This run starts from
  here; it does not start from turn 1." **No turn-number field and no default of 1** — position
  is entered as year/month/half, exactly the source's vocabulary.
- **Current state:** spinbuttons Speed, Stamina, Power, Guts, Wit, Energy, Fans, Skill Points;
  each with an "I don't know this value" checkbox. Copy: "Type a number when you have read it,
  tick 'I don't know this value' when the client does not show it, or leave it blank when you
  have not filled it in. The review says which of the three each field is."
- `Notes` textbox "Anything worth remembering about this career".
- "Recorded on their own screens": Legacy and support deck are **not** writable here; links to
  Legacy Lab and Support Cards ("re-entering them here would give the same fact two writers").
- Primary `Review snapshot`; `Back` link.

**Values entered + source trace.** Scenario=Unity Cup (§1.1); Trainee=Rice Shower (§1.1);
Year=Senior Year, Month=10, Half=Early (§1.1 "Senior Year Early Oct"); Speed 607, Stamina 577,
Power 549, Guts 736, Wit 397 (§1.2); Fans 209245 (§1.1/§1.15); Skill Points 173 (§1.2);
**Energy = "I don't know this value" ticked** (§1.7: client prints no number) — the UI's own
absent-state, not an invented figure. Notes left blank. Verified post-fill by read-only DOM
read: scenario `unity_cup`, trainee id 30, year 3, month 10, half Early; Energy checkbox
checked, all other absent-checkboxes unchecked.

**Bounded probe (one, §4.7).** Clicked `Review snapshot` with every field empty **before**
filling. Result: stayed on `/career/snapshot/setup`; focus moved to the Scenario select;
**native HTML5 constraint validation** — `select.required=true`,
`validationMessage="Please select an item in the list."`, `form.noValidate=false`; no
application-rendered inline error and the `role=status` paragraph empty. So the empty-submit
rejection is a browser bubble, not an in-page error message (experiential note, §8.3). No run
created; probe did not abandon the screen. Final submitted values are the source values above.

**Source data with no UI home (on this screen).** Mood (GREAT) — no mood field; the five
in-force caps (1325/1322/1368/1308/1800) and the scenario floor — no cap fields; stat grades
and growth rate — no fields; turns-remaining (5) — not asked (derived from position, to be
confirmed on review/cockpit); run number — correctly absent (source has none); deck and
ancestry — deliberately deferred to their own screens (stated, not a defect); team info,
roster, races, goals, skills — not on this form (later screens).

**Friction / errors / dead ends.** The native-validation bubble (probe) is the only friction;
no inline error text and no error summary for assistive tech beyond focus. Otherwise none.

**Outcome / next screen.** Clicked "Review snapshot" → S04 `/career/snapshot/review`.
Screenshot: `docs/audits/RS-UC-2026-11-snapshot-door/screens/S03-snapshot-setup-filled.png` (visually
reviewed: Unity Cup / Rice Shower / Senior Year·10·Early; stats 607/577/549/736/397; Energy
input greyed and disabled with its checkbox ticked; Fans 209245; SP 173; Notes empty).

### S04 — Snapshot review (`/career/snapshot/review`)

**Route and trigger.** From S03 "Review snapshot". Title / `h1` "Review snapshot". Stepper
Entry / Setup (links) / Review (current).

**What the screen asks for.** Read-back plus one confirmation. Copy: "This is exactly what
will be written. Read it back: a field marked **Unknown** is one the client did not show, and
a field marked **Not provided** is one you left alone. They are different statements, and the
run keeps them apart." Then `Position: Senior Year · Early October`; a list of the eight
numeric fields; checkbox "I understand this snapshot cannot be undone."; `Record snapshot`
(disabled until the checkbox is ticked); "Edit the snapshot" link.

**Values entered + source trace.** Read-back observed: Known · Speed 607, Stamina 577, Power
549, Guts 736, Wit 397; **Unknown · Energy**; Known · Fans 209245; Known · Skill Points 173.
Position read back as "Senior Year · Early October" — the source's position, not turn 1.
Ticked the understand-checkbox (a confirmation, not a probe); `Record snapshot` enabled.

**Source data with no UI home.** The review **does not read back Scenario or Trainee** — the
two identity selects entered on S03 are absent from the read-back even though the copy claims
"This is exactly what will be written." Mood, caps, grades, growth, team, races, goals, skills
and ancestry are likewise not on this read-back (they were never asked).

**Friction / errors / dead ends.** The identity omission above (experiential, §8.3): a user
cannot confirm scenario/trainee from the read-back. The Unknown/Not-provided distinction is
clear and correct.

**Outcome / next screen.** Clicked "Record snapshot" → S05 `/training-runs/2/cockpit`, title
"Career: Rice Shower". The application assigned internal run id **2**; no run number was
entered by the walk (source has none). Screenshot:
`docs/audits/RS-UC-2026-11-snapshot-door/screens/S04-snapshot-review.png`.

### S05 — Cockpit (`/training-runs/2/cockpit`)

**Route and trigger.** From S04 commit. Banner "Rice Shower · Unity Cup #2 · Cockpit",
"Turn 67 · Active"; `h1` "Rice Shower"; subline **"Senior Year · Early October · Turn 67 of
72"**. Flash status "Snapshot recorded." with Dismiss. Career nav: Cockpit, Training, Races,
Skills, Events, Inheritance, Deck, Timeline, and a non-link "Result" with "Result: Available
when the career is completed." Note: "This screen shows the run's position and decision. Full
scenario boards are on Scenario detail."

**What the screen asks for.** Nothing required; it is the run's hub. Regions: Stats and state,
Scenario, Advisor, Where to go; plus "Delete this run".

**Values entered + source trace.** None entered here. Rendered from the snapshot: Speed 607,
Stamina 577, Power 549, Guts 736, Wit 397; Fans 209,245; Skill Points 173; **Energy N/A**;
**Mood "N/A, not recorded"**. Position **Turn 67 of 72** — the source's position, confirming
the door carried it (§7.9).

**Source data with no UI home / rendered differently.**
- **Caps:** bars and the "Caps" line print **1,300 / 1,300 / 1,300 / 1,300 / 1,800** labelled
  "cap" — the **scenario floor**, not the source's in-force caps 1325/1322/1368/1308/1800.
  Copy: "No build target was set for this run, so bars are drawn against this run's ceilings:
  the ceilings you entered where you entered one, the scenario's own figure where you did
  not." The snapshot door offered **no cap field**, so the in-force caps have no entry path
  here and the floor is printed under the word "cap" (the two-vocabulary trap, P-06). Whether
  a cap field exists on Run settings is checked at that screen.
- **Mood GREAT:** no mood field on the snapshot door; cockpit shows "N/A, not recorded" with a
  "Mood effect at every tier" disclosure. Unrepresented at entry (U-).
- **Blue sparks / cap reconciliation:** "Blue sparks: No blue spark is recorded in this run's
  Legacy." and the panel copy explicitly states the source's non-reconciliation ("the blue
  sparks the tree carries account for the Guts extra and nothing else, so this panel shows the
  inputs without claiming one derives the other"). Legacy not yet entered, so sparks absent.
- **Scenario region:** Unity Cup, "Guide held"; table Finale **"5 turns away"**; Team "N/A, not
  recorded"; League "N/A"; Bursts "N/A"; Team rank "not recorded". The finale countdown matches
  the source's 5; the team block is empty pending Team setup.
- **Counters observed:** "Turn 67 of 72" (position); "Finale 5 turns away" and "Unity Cup finals
  is 5 turns away" (both point at the Unity Cup finals); "Junior Make Debut … fell due on turn
  12, 55 turns ago" (a past obligation). The client's **goal countdown** and **"Entry criteria
  met!"** are **not** on the cockpit (no Arima Kinen goal bar here).

**Friction / errors / dead ends.**
- **Advisor surfaces a Junior-year obligation as current on a Senior-year run:** "Junior Make
  Debut is due and not recorded. It fell due on turn 12, 55 turns ago." tagged `current`, with
  a Calculated badge ("the slot has arrived and no race_entries row points at it"). For a
  mid-career snapshot the debut is long past; the obligation list is not snapshot-aware.
- **Scenario mismatch in obligations:** the table lists **URA Finals Qualifier / Semifinal /
  Final (Aoharu)** as obligations on a **Unity Cup** run (each `unknown` / "no date", Calculated
  because `race_catalog_slots.turn` is null). URA Finale structure on a Unity Cup career.
- **Advisor self-contradiction on Energy:** it states "Not enough information: Energy is not
  recorded … Missing: Energy." yet "Why this answer" prints "The advisory line is **Energy 50**,
  read-as-is from a named source. Source: qualitative only, read 2026-11-02." A stated Energy
  of 50 alongside "Energy is not recorded" is contradictory (the 50 is the rest-modal figure,
  not a recorded current Energy).
- "Not enough information to recommend an action." — advisor declines, correctly, given absent
  Energy/target.

**Outcome / next screen.** Clicked the "Training" career-nav link → S06
`/training-runs/2/training`. Screenshot:
`docs/audits/RS-UC-2026-11-snapshot-door/screens/S05-cockpit.png`.

### S06 — Training decision (`/training-runs/2/training`)

**Route and trigger.** From S05 career-nav "Training". Banner "Rice Shower · Unity Cup #2 ·
Training", "Turn 67 · Active"; `h1` "Pick this turn's action"; subline "Five disciplines, rest,
recreation and an optional race. The advisor's recommendation is marked; nothing is committed
until you record the turn." "Back to cockpit" link. Same career nav; Result still gated.

**What the screen asks for.** A held action choice (radio): five disciplines (Speed, Stamina,
Power, Guts, Wit) plus Rest / Recreation / Race; then, only on commit, the client's post-turn
readings. Regions: Disciplines, Other actions, Why this action, Recorded outcome, and two
disclosures — "Client totals this turn (required to record)" and "Preview readings". Commit
controls: "Undo turn 67", "Discard", "Record turn 68".

**Values entered + source trace.** **None entered and no turn recorded.** The source is a
single mid-career snapshot at turn 67; it describes no turn-68 action and no post-turn totals.
Recording would advance the run to turn 68 and fabricate client totals the source never
showed, so the walk stops short of commit (stopping-point decision, §7.9). The disclosures were
opened read-only to inventory their fields:
- **Client totals:** `Turn *` prefilled **68** (the next turn, not 1 and not 67); `Speed *`,
  `Stamina *`, `Power *`, `Guts *`, `Wit *` empty with the snapshot's 607/577/549/736/397 shown
  as grey placeholders; `Speed level`…`Wit level` empty; `Outcome` (Choose/Success/Failure);
  `Energy state` (Exact/Band/Not recorded, default Exact); `Energy band` (Low/Mid/High, default
  Mid). Copy: "What the client shows after the turn resolves. This tool does not project these,
  so they start empty and the request refuses a turn that was not read."
- **Preview readings:** `Failure rate`, `Speed`, `Stamina`, `Power`, `Guts`, `Wit`,
  `Skill Points`, all empty. Copy: "What the client showed: the failure percentage and the
  gains it previewed. Enter a negative number when the preview showed a drop. Leave them blank
  when this turn did not read them."
- **Recorded outcome:** `Energy after` (placeholder `-`), `Mood after`
  (Unchanged/GREAT/GOOD/NORMAL/BAD/AWFUL), `Fans` (placeholder 209245), `Skill points`
  (placeholder 173). Copy: "These fields exist because the client shows them, not because the
  tool computes them. An empty field records 'not read off the screen', which is different from
  a zero."

**Source data with a home here, but only as a per-turn commit field.** The source's facility
levels (Speed 4 / Stamina 4 / Power 4 / Guts 5 / Wit 4), the training tile's preview
(+18 Speed / +8 Stamina / +6 Power, Failure 39%), the client's Energy band reading and the
current Mood (GREAT) all map to fields on this screen — `… level`, `Preview readings`,
`Energy state`/`Energy band`, `Mood after` — but every one of them is a **post-turn** field
written only when a turn is committed. There is no way to state them as the run's *current*
state without inventing a turn. So they remain unrepresented at entry (U-), and the
Disciplines panel correctly states the absence instead of guessing: each discipline reads
"Lv not recorded base" and "No facility level is recorded for this training on any turn this
run has logged, so the published per-level table has no row to read; a Level-1 figure would
state a level nobody entered." plus "Energy not recorded".

**What the screen states from the snapshot (correctly).** "Energy: Not recorded. A band is the
reading; the exact figure is not recorded. Wit costs no Energy. Figures are the published Lv
base the run recorded for each facility, before multipliers." Discipline feed lines: Speed
"Feeds Power", Stamina "Feeds Guts", Power "Feeds Stamina", Guts "Feeds Speed and Power", Wit
"Feeds Speed" + a "no Energy" chip. **Why this action** prints the rest distribution: "Rest pays
+50 on 58.1% of results, +70 on 25.4%, +30 on 12.8%, and +30 with Late-Night Feeling on 3.7%
(n=3,833)." and "Below 50 Energy the failure curve changes character rather than degrading
linearly, which is why the advisory line sits where it does." This matches the source's
rest-tier vocabulary (30/50/70, 50 modal) and its advisory-line reasoning.

**Friction / errors / dead ends.**
- **Doubled word in the held-choice line:** "No action selected **selected** · 1-5 pick · Enter
  records · Esc back" (visible in the screenshot and in the accessibility tree). Copy defect.
- **Turn-index convention:** the commit button and `Turn *` say **68** while the banner and the
  source tile sit at **67**. The tool treats the snapshot position as "the turn already
  decided" and the next record as 68; the source's tile is labelled turn 67. Not resolved on
  the source's behalf (§7.9); recorded as a convention difference, not corrected.
- **`Energy after` placeholder is a bare `-`**, the one dash-shaped hint on an otherwise
  N/A-disciplined surface (minor copy note).
- No advisor recommendation is marked on any discipline (the cockpit advisor declined for lack
  of Energy), consistent with S05; not a defect here.

**Outcome / next screen.** Clicked the "Races" career-nav link → S07
`/training-runs/2/races`. Screenshot:
`docs/audits/RS-UC-2026-11-snapshot-door/screens/S06-training.png` (visually reviewed: five discipline cards
each stating the level absence, rest-tier list, empty Client totals with Turn 68, empty Preview
readings, doubled "selected" line).

### S07 — Race decision (`/training-runs/2/races`)

**Route and trigger.** From S06 career-nav "Races". Banner "Rice Shower · Unity Cup #2 · Races"
with only "Active" (no turn figure, unlike S05/S06); `h1` "Enter a race, or skip the turn";
subline "Races pay fans, Skill Points and stat gains; a training turn usually pays more stats.
Pick the race or close this screen." "Back to cockpit" link. **The career nav here renders
"Result" as a live link** (`/training-runs/2/result`) with no gating copy, whereas S05/S06
rendered it as a non-link with "Result: Available when the career is completed." (inconsistent
gating presentation; the Result route itself is visited later).

**What the screen asks for.** Pick one of the current turn's calendar races (or none), then
either record its result or enter the race; plus an off-calendar recorder. Regions:
- **Available this turn** — table of 11 races at this turn, columns Race / Grade /
  Surface·distance·aptitude / Fans needed / Skill Pt / action. Copy: "The calendar's races at
  this turn. Every row states the grade, the surface and the distance band, and the trainee's
  own letter for that axis." Rows: Fuchu Umamusume Stakes G2 Turf·Mile C 1800 +35; Green Channel
  Cup OP Dirt·Sprint E 350 +30; Kyoto Daishoten G2 Turf·Medium A 2000 +35; Ladies' Prelude G2
  Dirt·Mile C 1800 +35; M.C. Nambu Hai G1 Dirt·Mile C 12000 +45; Mainichi Okan G2 Turf·Mile C
  2000 +35; October Stakes OP Turf·Medium A 350 +30; Opal Stakes OP Turf·Sprint E 350 +30;
  Shinetsu Stakes OP Turf·Sprint E 350 +30; Tokyo Hai G2 Dirt·Sprint E 1800 +35; Uzumasa Stakes
  OP Dirt·Mile C 350 +30. **Every row's action is a disabled button "Maiden lock not cleared"**
  with "The maiden lock is not cleared; this race opens only once a Debut or Maiden is won."
- **Selected-race detail** (first row preselected): Grade / Distance / Aptitude ("Turf A ·
  Mile C") / Skill Points; "Placement payouts" table (1st 35 SP +8 stat, fans "scales"; 2nd/3rd
  `N/A` titled "Only the winner's payout is published"); copy "Only the 1st-place figure is
  sourced. A first-place finish at 0 Race Bonus pays G1 +10 stat / +45 SP, G2-G3 +8 / +35 and
  OP-Pre-OP +5 / +30; Trackblazer pays the same stat at a lower SP. Second and third are not
  published, so those cells stay blank rather than extrapolated." and "The 34-percent Race
  Bonus breakpoint is the one the guide names for URA Finale and Unity Cup; the 50-percent row
  is Trackblazer's. A planner must print the payout the client shows rather than recompute it."
  Disclosure "Record the result of this race" → `Placement` spinbutton + "Record result".
- **Consecutive races** — "Races in a row so far: not recorded"; copy "Races in a row raise the
  failure risk. Trackblazer publishes the odds; URA Finale and Unity Cup do not, so this screen
  states the count and not a probability." + "No probability is printed for this scenario.
  Inventing one would be the 'score' the app is forbidden to add."
- **Commit bar** — status "Fuchu Umamusume Stakes selected"; disabled "Undo turn 18";
  "Train instead" link; disabled "Enter a race".
- **Race not on the calendar** disclosure → `Race title *` (placeholder "e.g. Practice Race"),
  `Month *` (1–12, default 10), `Half *` (select/Early/Late, default Early), `Tier (optional)`,
  `Outcome` (NotOffered/Skipped/Entered/Completed, default NotOffered), `Placement`,
  `Fans gained`, "Record race". **No Year field.**

**Values entered + source trace.** No current-turn race entered (the source's turn-67 tile is a
training action, and every calendar row is locked anyway). Four off-calendar records submitted,
each accepted with status "Race recorded.":
1. `Race title`="Nikkei Sho", `Month`=3, `Half`=Late, `Outcome`=Completed; Tier/Placement/Fans
   gained left blank. Source: sheet §2.3 "Cleared objectives" (doc §1.6 @209, §1.15 @468) —
   Nikkei Sho top 3, Senior Late Mar, `CLEAR!`. Placement blank because "top 3" is the goal
   criterion, not a stated finishing position.
2. `Race title`="Tenno Sho (Spring)", `Month`=4, `Half`=Late, `Outcome`=Completed,
   `Placement`=1. Source: same row — objective "1st" with `CLEAR!`; placement 1 is derived
   (criterion 1st + cleared ⇒ finished 1st), shown here as a derivation, not a read value.
3. `Race title`="Takarazuka Kinen", `Month`=6, `Half`=Late, `Outcome`=Completed; Placement blank
   (top-3 criterion). Source: same row, Senior Late Jun.
4. `Race title`="Arima Kinen", `Month`=12, `Half`=Late, `Outcome`=Entered; Placement/Fans blank.
   Source: sheet §2.3 "Active objective" / "Pending race" (doc §1.6 @209, §1.15 @468) — Senior
   Late Dec, `Entry criteria met!`, `In progress`, `Turns left: 5`. "Entered" is the pending
   state the form offers.
All four are Senior-year months, so the missing Year field does not bite here; it would for
cross-year history.

**Source data with no UI home / rendered differently.**
- **The whole current-turn calendar is unusable on this snapshot.** The maiden lock keys on a
  recorded Debut/Maiden win; the snapshot door imports no race history, so a Senior-year run
  with 209,245 fans and three cleared G1/G2 objectives still reads "maiden lock not cleared"
  and all 11 races are disabled. Recording the three Completed objectives above did **not**
  clear it (they are not Debut/Maiden wins), and the source states no debut row to enter.
  Functional blocker for the race desk's primary path on a mid-career import (F-).
- **Aptitude letters differ from the client's.** The table and detail print Mile **C**,
  Sprint **E**, Medium **A** ("the race's aptitude letter for this distance and surface"),
  while the source's client letters (sheet §2.3, rule 30) are Mile **B** / Dirt **C** / End **C**
  with Front A / Late B. The app's per-axis letters are not the client's printed set; recorded
  as a disagreement, not reconciled (D-/U-).
- **No read-back of recorded races on this screen.** After four "Race recorded." statuses the
  screen shows no list of them; the form keeps the last entered values (Arima Kinen / 12 / Late
  / Entered) rather than resetting, so a second click would duplicate. Persistence is checked
  on Timeline (S12), not here.
- **Consecutive-race count stays "not recorded"** despite three Completed records at past
  months; the off-calendar entries do not feed it.
- **Goal countdown / "Entry criteria met!"** are not on this screen (they were absent from the
  cockpit too); the pending final is representable only as the off-calendar "Entered" row.

**Friction / errors / dead ends.**
- **"Undo turn 18"** on a run whose position is turn 67 (S05/S06 said "Undo turn 67" / "Record
  turn 68"). The race desk's undo control names a turn the run never had; disabled, so no
  action was possible, but the index is wrong (F-).
- **Result gating inconsistency** (live link here vs gated non-link on S05/S06).
- **Banner drops the turn** ("Active" only) while sibling screens print "Turn 67 · Active".
- The disabled "Enter a race" and every disabled maiden-lock button are correctly signalled as
  disabled with an explanation, so this is a data/model dead end, not a silent control.

**Outcome / next screen.** Exited via the "Skills" career-nav link → S08. Screenshot:
`docs/audits/RS-UC-2026-11-snapshot-door/screens/S07-races.png` (visually reviewed: 11 maiden-locked rows,
Fuchu detail with N/A 2nd/3rd payouts, "Undo turn 18", off-calendar form holding Arima Kinen).

### S08 — Skills planner (`/training-runs/2/skills`)

**Route and trigger.** `/training-runs/2/skills`, reached by the "Skills" career-nav link from
S07. Banner "Rice Shower · Unity Cup #2 · Skills" + `Active`; h1 "Spend 173 Skill Points";
sub-copy states the hint ladder 10/20/30/35/40 and that an unearned hint buys at full price.
Career nav lists all nine screens; "Back to cockpit" link present.

**What the screen asks for.** A Skill Point budget bar (Skill Points 173 / Planned spend 0 /
Remaining 173, "Learn planned skills" disabled); a Search box plus disabled Type and Hints
comboboxes and a Clear button; a Skills table (checkbox, Skill, Family, Base, Discount, Cost,
state); a "Why the hint ladder is not linear" table (five captions) with a reconciliation note;
a plan status strip ("No skill planned", "Clear plan"); and a collapsed group "Build target,
recorded skills and the add form" holding: Your target, Skill Point coverage (∑ Calculated),
Purchasable skills (∑ Calculated), Recommended (Calculated), Hint discounts, Required,
Available, Learned, Inherited, a save-status line, Add a skill (autocomplete + "Add to run"),
and Acquired / skipped (per-skill status combobox + Turn acquired + "Save acquisition status").

**Values entered and source trace.** One source-obtained skill was demonstrated end to end:
- Typed `Steadfast` into "Skill to add", picked the `Steadfast` option, clicked "Add to run" →
  status **"Skill status saved."**; the Skills table gained a row (Family Standard, Base 160,
  Discount N/A, Cost 160, state Suggested) and the Available column gained a Steadfast card.
  Trace: sheet §2.3d Obtained list, first entry "Steadfast".
- Set "Change status for Steadfast" to **Acquired**, left "Turn acquired for Steadfast" blank
  (the source states no acquisition turn), clicked "Save acquisition status" → status
  **"Skill status saved."**; the table state cell became `Acquired`, the card moved from
  Available to **Learned** ("Recorded: Acquired"), and the Acquired/skipped row's Current
  status became `Acquired`. Trace: same Obtained list entry.
- No other skill was added, no plan checkbox was ticked, no Level value was set, and no build
  target was created. The remaining nine Obtained entries and the 33-row price fixture were
  **not** entered (the sheet is the reference; one skill suffices to exercise the path).

**Source data with no UI home.**
- **Hint levels.** The 33-row fixture pairs each skill with a hint level (Lv1..Lv4, LvMax,
  none). The screen prints the ladder and says "a run records no hint level here, so no
  discount is applied"; the Discount cell is `N/A`. After acquisition the Learned card exposes
  a "Level" spinbutton captioned "of 6" while the adjacent line still reads "Hint level: N/A ·
  not in this run's pool" — an ambiguous control whose ceiling (6) does not match the five
  captions printed on the same screen. Left untouched; the source's per-skill hint levels have
  no confirmed home (U-).
- **The other nine Obtained skills** (Deep Breaths, Extra Tank, Shrewd Step, Nimble Navigator,
  Corner Recovery ○, Corner Adept ○, Swinging Maestro, Blue Rose Closer, Red Shift/LP1211-M)
  are representable by the same add+Acquired path but were not entered; only one was needed to
  prove the path. Not a gap in the UI, a scope decision.
- **Inherited skills.** The Inherited column states outright: "No column holds the skills a
  Legacy configuration passes down… the client's own inherited list is where a Trainer reads
  them." The source's inherited/ancestry skills (Cut and Drive!, Resplendent Red Ace sold by
  the shop) have no home here (U-).
- **Skill tints** (olive-gold, green-to-pink rainbow) and the **Obtained** pink label from the
  client Learn screen have no representation; the app uses a plain `Acquired` pill (U-, copy).
- **The in-career rail** (Jukebox, Sparks, Log, Career Profile, Agenda, Item Request, Menu) and
  "There is no log." are client-only; not this screen's responsibility (context, not a gap).

**Friction / errors / dead ends.**
- **Recommended region contradicts the recorded SP.** It prints "No recommendation can be
  generated: the run has no recorded SP, or all skills are already acquired" while the budget
  bar and the Purchasable paragraph both print 173 SP recorded. The disjunction's first branch
  is false on this run; the message misdiagnoses why no recommendation exists (the real reason
  is no build target / nothing Required) (F-).
- **Purchasable list reads the full catalogue, not the run's pool.** It says so explicitly
  ("This list reads the full catalogue, not this run's pool") and totals 476 priced skills /
  7140 SP / shortfall 6967 against 173 SP. On a mid-career run whose client pool is a few dozen
  rows, this list is not actionable; the shortfall figure is catalogue-wide, not run-wide (F-).
- **Available-column copy contradicts the recorded state.** After Steadfast was marked
  Acquired the Available column reads "No skill is marked for this run yet. This planner is
  where a skill state is marked." One skill *is* marked (Acquired, in Learned); the sentence
  only holds for the Available subset and reads as a whole-run claim (F-, copy).
- **Raw condition expression leaked into the Skills table.** The Skill cell prints
  `distance_type==3&is_finalcorner==1&overtake_target_time>=2` under the name — an internal
  predicate shown to the Trainer (F-, copy/leak).
- **JP skill name printed.** The Available/Learned card heading is "Steadfast 食い下がり";
  the Global client vocabulary rule prints English names; the JP gloss is source data leaking
  into display copy (F-, copy/lore-adjacent).
- **Type and Hints filters are disabled** with only an "Any" option, so the two filter controls
  are present but inert on this run (F-).
- **"1 skills · page 1 of 1"** pluralization (F-, copy).
- **Build target not settable here.** "Your target" and "Required" both say this screen
  "reorders the priorities a target names; it does not add any", so the cap/target entry the
  cockpit's floor-as-cap finding points at must live on another screen (Run settings, still to
  visit). Not a defect of this screen; recorded as a pointer.
- The transient "Skill status saved." banner was present in the post-submit accessibility
  snapshot but had cleared by the screenshot moments later; the persisted state (Acquired
  pill, Learned card) remained, so persistence is confirmed by state, not by the banner.

**Outcome / next screen.** Exited via the "Events" career-nav link → S09. Screenshot:
`docs/audits/RS-UC-2026-11-snapshot-door/screens/S08-skills.png` (visually reviewed: budget 173/0/173,
Steadfast row with Acquired pill and the raw condition line, hint-ladder table, Purchasable
list, Learned card with the blank "Level … of 6" spinbutton, Acquired/skipped row).

### S09 — Event decision (`/training-runs/2/events`)

**Route and trigger.** `/training-runs/2/events`, reached by the "Events" career-nav link from
S08. Banner "Rice Shower · Unity Cup #2 · Events" + `Active`; h1 "A training event is asking";
sub-copy: "Pick one option. The event resolves immediately and cannot be re-run; an Alarm Clock
retries a lost goal race, not an event."

**What the screen asks for.** A two-column layout. Left "What the event shows": a "Name the
event as the client shows it" card badged `Source not set`, with copy stating the tool does not
transcribe or paraphrase client event text; an Options textbox "The option's own words"
(placeholder "Type the option the client showed", caption "Selecting shown; outcome not
predicted."); "No recorded choices for this event yet."; a note that no option predicts its
outcome and the app is forbidden to invent a score/percentage/best pick; and a collapsed
"Where this event came from (required to record)" group which, expanded, holds Turn* (prefilled
**68**), Source* (Choose/Character/Support Card/Group/Scenario), Event name*, Support card.
Right "Record the outcome": Energy / Mood / Skill points / Stat changed all `Not recorded`,
with the explanation "No column holds an event's Energy, Mood, Skill-Point or stat delta… The
note and the bond change are the two outcome fields this tool can store."; Bond change
spinbutton and Note textbox (both Optional); a note that an empty field records "not read off
the screen", different from zero. Below, "This event's provenance": Source=Client screen,
Effect table=`Not published`, Retryable="No: Alarm Clock covers goal races only". Action strip:
status "No option selected", "Undo turn 67", "Skip" (→ cockpit), "Record event". A collapsed
"Recorded events on this run" group which, expanded, reads "No event choices recorded yet for
this run…".

**Values entered and source trace.** **None.** The pre-click data sheet (§2.3) carries no event
transcription: the source's frames show the Learn, Career and Career-Profile screens, not an
event dialog, and no event name, option words, source category or outcome delta is published.
The screen itself forbids inventing event copy, so entering anything here would fabricate
source data. Both disclosures were expanded (read-only) to record their required/empty states;
no field was filled and neither "Record event" nor "Undo turn 67" was pressed.

**Source data with no UI home.** Not applicable in the usual direction: here the *screen* asks
for data the *source* does not provide. Conversely, the screen honestly admits four outcome
deltas it cannot store (Energy, Mood, Skill points, Stat changed) — had the source published an
event's effects, those four would have no home (U-). Bond change and Note are the only storable
outcome fields.

**Friction / errors / dead ends.**
- **"Record event" renders enabled with nothing selected.** The action strip says "No option
  selected" and the required provenance fields (Source*, Event name*) are empty, yet the button
  is not disabled. Not exercised: submitting would either raise validation or create an
  unsourced event record, and the brief forbids probes that create orphaned state (F-,
  observed-not-exercised).
- **Disclosure caret does not flip on expand.** Both "Where this event came from" and "Recorded
  events on this run" keep the `▸` glyph while their bodies are visible (same behaviour as the
  S08 group), so the caret does not signal open state (E-, spans S08/S09).
- **Turn* prefilled 68** matches the S06 next-record convention (position 67 → next 68); correct
  here, and the "Undo turn 67" label is correct on this screen — contrasting with S07's wrong
  "Undo turn 18". So the undo-index defect is races-screen-specific, not global.
- The `Source not set` badge and the "Not published" effect-table pill are honest absent-state
  markers, not defaults; no friction.

**Outcome / next screen.** Clicked the "Inheritance" career-nav link → S10 `/training-runs/2/inheritance`. Screenshot:
`docs/audits/RS-UC-2026-11-snapshot-door/screens/S09-events.png` (visually reviewed: two-column event form,
four `Not recorded` outcome deltas, provenance table, enabled "Record event" beside "No option
selected", expanded provenance fields with Turn 68, empty recorded-events note).

### S10 — Inheritance (`/training-runs/2/inheritance`)

**Route and trigger.** `/training-runs/2/inheritance`, reached by the "Inheritance" career-nav link from
S09. Banner "Rice Shower · Unity Cup #2 · Inheritance" + `Active`; h1 "Choose what to inherit";
sub-copy: "The inheritance moment happens at career start, Classic and Senior. Pick the spark you want carried f…" (truncated). Career nav lists all nine screens; "Back to cockpit" link not visible here (nav rail only).

**What the screen asks for.** A six-ancestor grid (two grandparents behind each parent) with open slots for Parent A, Parent B, and four Grandparents; a Compatibility field ("Not recorded / raises trigger chance and payout"); per-parent spark listings (collapsed "No parent recorded for this slot"); a "Result of this pick" table showing stat caps (all scenario floor values with "N/A · this tool computes no inheritance change"), Blue Sparks (0 recorded · 0★), Aptitude Shifts (none), Skill Hints (none); an explanatory note about cap raises and the two-★3-parent loop; and action buttons "No legacy selection recorded", "Skip this moment", "Record inheritance", plus "Record what this moment granted (required to record)" disclosure and "Observed inheritance events (0)" / "Inheritance milestones" groups.

**Values entered and source trace.** **None.** The source's inheritance data (sheet §2.3b: Vodka B+, Maruzensky UG Guest, and four grandparents with full spark chains) is available but the screen requires a *single legacy selection per run* and states "One selection per run is what this tool stores. The moment grants one spark per parent, but the run carries a single legacy selection, so this screen lists the sparks each parent carries and the run's recorded selection rather than promising per-parent radios the column cannot hold." The source describes six ancestors each carrying multiple blue/white sparks; representing that as one selection would lose the per-ancestor granularity. No field was filled and neither "Record inheritance" nor "Skip this moment" was pressed.

**Source data with no UI home.**
- **Per-ancestor spark chains.** The screen's model (one legacy selection per run) cannot represent the source's six-ancestor, 24+ spark chain data (sheet §2.3b). The "Blue sparks" line reads "0 recorded · 0★" and the note explicitly disclaims computation. The source's detailed spark chains have no home here (U-).
- **Cap raises from ancestry.** The result table prints only scenario floor caps (1300/1300/1300/1300/1800) with "N/A · this tool computes no inheritance change". The source's in-force caps (1325/1322/1368/1308/1800) already exceed the floor where entered, but inheritance's contribution to the in-force caps cannot be entered or displayed (U-).
- **Aptitude shifts and skill hints from ancestry.** The result table rows for these read "none recorded this pick" / "none recorded this pick". The source's aptitude letters and skill hints from ancestry have no entry path (U-).
- **Guest flag on Maruzensky.** The source flags Maruzensky as Guest; the screen has no Guest field (U-).
- **Compatibility value.** The Compatibility field reads "Not recorded" with no input; the source provides no compatibility number (U-).

**Friction / errors / dead ends.**
- **Truncated sub-copy in banner.** The sub-copy under h1 is cut off mid-sentence ("Pick the spark you want carried f…") — likely a CSS line-clamp or fixed-height container (E-).
- **No "Back to cockpit" link in content area.** The career nav rail provides navigation but no contextual back link in the main content (E-, consistency with other screens).
- **Single-selection model vs six-ancestor source.** The screen's architecture decision (one legacy selection per run) is stated honestly, but it means a mid-career import with full ancestry cannot be represented faithfully. Not a defect of this screen per se; recorded as a representation gap (U-).
- **"Record inheritance" enabled with nothing selected.** The button is not disabled while no parent/grandparent is chosen and no spark is picked; submitting would create an empty selection record. Not exercised (F-, observed-not-exercised).

**Outcome / next screen.** Clicked the "Deck" career-nav link → S11 `/training-runs/2/deck`. Screenshot:
`docs/audits/RS-UC-2026-11-snapshot-door/screens/S10-inheritance.png` (visually reviewed: six-ancestor grid all open, Compatibility "Not recorded", result table with scenario floors and N/A deltas, action buttons enabled).

### S11 — Deck (`/training-runs/2/deck`)

**Route and trigger.** `/training-runs/2/deck`, reached by the "Deck" career-nav link from S10. Banner "Rice Shower · Unity Cup #2 · Deck" + `Active`; h1 "This run's six slots"; sub-copy: "The deck is run-scoped: changing it here does not touch any other career. Effects apply from the next turn." Career nav lists all nine screens with "Result" gated as "Available when the career is completed." "Back to cockpit" link present.

**What the screen asks for.** Six slot cards (Slot 1–5 plus Slot 6 · Friends), each showing "Not equipped", an Ownership selector (Owned / Rented / N/A, default N/A), and a "Choose a card in Slot N" autocomplete; an explanatory note that ownership flag is saved with the deck and stays on the run; "0 of 6 slots filled" counter; "Cancel" and "Save deck" (warning: "Saving replaces the whole deck: slots left empty are cleared."); and a collapsed "Choose a card" / "Deck analysis" group.

**Values entered and source trace.** **None.** The source deck (sheet §2.3: six specific SSR cards with names, stats, levels, and one borrowed Friend) is fully specified but the screen was not filled. The walk demonstrates the empty state; populating six cards with ownership flags is a data-entry task, not a path-exploration requirement. No field was filled and "Save deck" was not pressed.

**Source data with no UI home.**
- **Card levels and stats.** The source specifies each card's level (30/30, 35/35, 50/50) and stat type (Speed, Stamina, Guts). The screen's autocomplete picks a card; level and stat are not additional fields on the deck screen (they live on the card's catalogue row). Representable by card selection alone; not a gap.
- **Borrowed / Friend flag.** The source marks Super Creek as "borrowed (Friends pill)". The deck screen has Slot 6 labelled "Friends slot" with the same ownership selector (Owned/Rented/N/A). The borrowed state maps to the Friends slot + Ownership=Rented (or a dedicated flag); not exercised.
- **Unique Perks and scenario-exclusive badges.** The source notes Nishino Flower's "Unique Perk" and "Unity Cup Exclusive" badge (sheet §2.3f). These are card-catalogue properties, not deck fields; not a gap.
- **Support Effects breakdown.** The source lists Friendship Bonus 15%, Mood Effect 20%, Training Effectiveness 5% (sheet §2.3f). These are per-card catalogue data; not a deck-screen field.

**Friction / errors / dead ends.**
- **Ownership default N/A reads as "not classified" rather than "Owned".** The note explains this correctly ("A slot you have not classified reads as N/A rather than as Owned"), but a Trainer importing a known deck must explicitly set each slot's ownership; no bulk or prefill from catalogue (E-, workflow).
- **"Save deck" replaces the whole deck.** The warning is clear, but partial updates are not supported; changing one slot requires re-selecting all six (E-, workflow).
- **No read-back of the source's six cards.** The screen does not show the source deck's card names/stats as a reference; the Trainer must recall or cross-reference (E-, workflow).
- **Deck analysis collapsed group purpose unclear.** The "Choose a card" / "Deck analysis" group is collapsed with no preview; its function is not self-evident (E-, discoverability).

**Outcome / next screen.** Clicked the "Timeline" career-nav link → S12 `/training-runs/2/timeline`. Screenshot:
`docs/audits/RS-UC-2026-11-snapshot-door/screens/S11-deck.png` (visually reviewed: six empty slots with Ownership N/A, "0 of 6 slots filled", save/replace warning).

### S12 — Timeline (`/training-runs/2/timeline`)

**Route and trigger.** `/training-runs/2/timeline`, reached by the "Timeline" career-nav link from S11. Banner "Rice Shower · Unity Cup #2 · Timeline" + `Active`; h1 "Every logged turn, race and event"; sub-copy: "Rice Shower's Unity Cup career, in order. 1 entries recorded. This is the auditable record behi…" (truncated). Career nav lists all nine screens; filters: Kind (All entries), Turn, Year (All years / Senior), Clear; "Phase grouping" collapsed sections for Junior Year (0 entries), Classic Year (0 entries), Senior Year (1 entries), After the grid · finale block (0).

**What the screen asks for.** A filterable table of logged entries with columns Turn / Position / Kind / What Happened / Energy / SP. Currently shows one row: Turn 67, Position "Senior · Early Oct", Kind "Turn", What Happened "Turn recorded · Recorded as entered / Edit turn 67", Energy "N/A", SP "173". "Phase grouping" discloses the phase structure (Junior 1-24, Classic, Senior, finale block). "Edit turn 67" link on the row.

**Values entered and source trace.** The single row reflects the snapshot-recorded turn 67 (position, SP 173, Energy N/A). The four off-calendar races recorded on S07 (Nikkei Sho, Tenno Sho Spring, Takarazuka Kinen, Arima Kinen) do **not** appear in the timeline — they were recorded via the off-calendar form on the Races screen, which does not create timeline entries. The timeline only shows turns recorded via the Training screen's commit flow.

**Source data with no UI home.**
- **Off-calendar race records.** The four races entered on S07 have no timeline representation. They exist in the database but the timeline query does not surface them (U-).
- **Turn 67 Energy "N/A".** Correctly reflects the snapshot's "I don't know this value" (sheet §2.3). The timeline prints "N/A" not zero, which is the correct absent-state semantics.
- **No event rows.** The source has no event transcription; none expected.

**Friction / errors / dead ends.**
- **Off-calendar races invisible on timeline.** The four Completed/Entered races recorded on S07 are absent from the auditable record. A Trainer reviewing the timeline would not see the three cleared G1/G2 objectives or the pending Arima Kinen. This is a data-model gap: off-calendar races are not joined into the timeline (F-).
- **"Edit turn 67" link goes to training commit with Turn 68.** The timeline's "Edit turn 67" navigates to `/training-runs/2/training` where the commit button says "Record turn 68" and the Turn field is prefilled 68. The timeline labels it turn 67 (the snapshot position); the training screen treats it as the next record. Convention difference, not resolved (D-, cross-screen).
- **Sub-copy truncated.** "This is the auditable record behi…" cut off (E-, same as S10).
- **Junior/Classic phases show "0 entries" with "Duration unknown for most entries".** The phase grouping is accurate for this run (no turns recorded there), but the copy "Duration unknown for most entries; the Debut race is the one dated obligation" implies missing data rather than "not recorded because the snapshot started at Senior". Accurate but potentially confusing (E-, copy).

**Outcome / next screen.** Clicked the "Result" career-nav link → S13 `/training-runs/2/result`. Screenshot:
`docs/audits/RS-UC-2026-11-snapshot-door/screens/S12-timeline.png` (visually reviewed: single turn-67 row, phase grouping, filters, off-calendar races absent).

### S13 — Result (`/training-runs/2/result`)

**Route and trigger.** `/training-runs/2/result`, reached by the "Result" career-nav link from S12. Banner "Rice Shower · Unity Cup #2 · Result" + `Active`; h1 "Rice Shower · Unity Cup"; sub-copy: "This career is still running / This career is still running, so there is no result to read yet. The result screen reads a run whose status is Completed; until then the Cockpit is where the next turn is decided and where the status changes." Links: "Back to the Cockpit", "Career Timeline", "Cockpit".

**What the screen asks for.** Nothing. It is a gated screen that only renders content when the run's status is Completed. Current run status is Active (turn 67 of 72), so the screen shows the "still running" message.

**Values entered and source trace.** None. The source is a mid-career snapshot at turn 67; the career has not completed and no result exists.

**Source data with no UI home.** Not applicable — the screen correctly refuses to fabricate a result.

**Friction / errors / dead ends.**
- **Career-nav gating inconsistency resolved.** On S05/S06 the "Result" nav item was a non-link with "Result: Available when the career is completed." On S07/S08/S09/S10/S11/S12 it was a live link to `/training-runs/2/result`. On S13 the screen itself enforces the gate by showing "This career is still running". The inconsistency is in the nav presentation (link vs non-link), not in the screen's logic. Recorded as S07 finding.
- **No preview or projection.** The screen does not show a "projected result" or "current standings" for an in-progress run. The source's active objective (Place 1st in Arima Kinen, Turns left: 5) and cleared objectives are not summarized here. Not a defect; the screen's contract is completed-run only.

**Outcome / next screen.** **Walk end.** The primary pass has visited all nine career-nav screens (Cockpit, Training, Races, Skills, Events, Inheritance, Deck, Timeline, Result) and the four snapshot-import screens (Dashboard, Snapshot entry, Snapshot setup, Snapshot review). No further screens in scope. Screenshot:
`docs/audits/RS-UC-2026-11-snapshot-door/screens/S13-result.png` (visually reviewed: "This career is still running" message, three back links).

## 7.4 Primary-pass scope and stopping point

**Scope completed.** The walk covered:
- The three dashboard doors and the snapshot-import flow (S01–S04).
- The in-career cockpit and all eight decision screens reachable from the career nav (S05–S13).
- Every screen listed in the screen ledger (§7.2), each visited once in forward order, no backtracking.

**Stopping point.** The walk stops at S13 (Result screen) because:
1. The run is mid-career (turn 67 of 72, Active); the Result screen correctly shows no result.
2. No further career-nav screens exist.
3. Recording a turn (Training → commit) would advance the run to turn 68 and require inventing post-turn client totals the source does not provide — forbidden by the no-seeding rule (§1.0, item 9) and the brief's stopping-point instruction.
4. The source document is a single snapshot at Senior Year Early Oct; it carries no turn-68 action, no event transcription, no inheritance selection moment, and no deck entry. The walk has exercised every *read* path and demonstrated the *write* paths' empty states without fabricating data.

**Primary-pass scope / stopping point (header item):** All nine career-nav screens visited in forward order; snapshot import completed; no turn recorded, no event recorded, no inheritance recorded, no deck saved — all write paths demonstrated at empty state per the source's snapshot-only scope.

## 7.5 Door and turn index used

**Door chosen at S02.** "Import an existing career" → `/career/snapshot` (door 2 of 3). The screen's vocabulary ("already underway", "exactly as far along as your client shows it", "not turn 1") matches the source's "Senior Year Early Oct" mid-career position.

**Turn index carried by the door.** The snapshot setup (S03) accepts position as Year/Month/Half (Senior / 10 / Early), **not a turn number**. The application derives the turn index from the scenario's calendar: Unity Cup Senior Year Early Oct = turn 67 of 72. This derivation was confirmed on the Cockpit (S05) which renders "Turn 67 of 72" and "Senior Year · Early October · Turn 67 of 72". The source's "turn 67" and "5 turns remaining" match the derived position exactly.

**Turn convention on decision screens.** The Training screen (S06), Events screen (S09), and Inheritance screen (S10) prefill the next record's Turn as **68** (position 67 → next record 68). The Races screen (S07) incorrectly showed "Undo turn 18". The Timeline (S12) labels the snapshot row as Turn 67. The convention difference (position vs next-record) is recorded as a cross-screen inconsistency (§7.3 S06, S07, S10, S12) but the door's position was correctly carried.

## 7.6 Walk end

**Walk end:** 2026-11-07 11:54:00 +0800 (Asia/Kuala_Lumpur). Duration ~1h 06m.
**Total screens visited:** 13 (S01–S13).
**Screenshots captured:** 13 files in `docs/audits/RS-UC-2026-11-snapshot-door/screens/`.

## 7.7 Open items (§2.4 carried forward)

1. **Dirt / End aptitude:** brief sheet Dirt G / End G; frame `1dde6b2f` corrects to Dirt C / End C. Frame wins → C / C entered; open.
2. **Vodka Power spark:** in-career ★★☆; doc post-career ★☆☆. In-career entered; post-career open (doc §6 item 10 / §11).
3. **Daiwa L1 Guts / Resplendent Red Ace:** brief ★★☆; doc §1.5 ★☆☆ on both branches. Brief entered; doc open.
4. **Maruzensky rank `UG` vs trainer's `UG1`:** no higher-resolution frame exists in this tree (`Glob **/*24f5b5d0*` → none). Frame reading `UG` entered; `UG1` open; inability recorded.
5. **Per-ancestor spark chains have no home on Inheritance screen** (S10 finding): the screen's one-selection-per-run model cannot represent six ancestors × multiple sparks.
6. **Off-calendar races invisible on Timeline** (S12 finding): four races recorded on S07 do not appear in the auditable record.
7. **Result nav gating inconsistent** (S07/S13 finding): live link on some screens, non-link on others; screen enforces gate correctly.
8. **Training screen "Undo turn 18" on a turn-67 run** (S07 finding): races-screen-specific undo index defect.
9. **Cockpit Advisor surfaces Junior obligation on Senior run** (S05 finding): obligation list not snapshot-aware.
10. **Cockpit Advisor URA Finale obligations on Unity Cup run** (S05 finding): obligation table not scenario-aware.
11. **Cockpit Advisor Energy self-contradiction** (S05 finding): "Energy not recorded" vs stated "Energy 50".
12. **Scenario caps printed as "cap" on Cockpit** (S05 finding): in-force caps unrepresented; floor shown under "cap" label.
13. **Skills planner Recommended region misdiagnoses SP state** (S08 finding): claims "no recorded SP" while budget bar shows 173.
14. **Skills planner Purchasable list reads full catalogue** (S08 finding): not run's pool; shortfall figure catalogue-wide.
15. **Skills planner Available-column copy contradicts state** (S08 finding): "No skill marked" after one Acquired.
16. **Skills planner raw condition expression leaked** (S08 finding): internal predicate shown in Skill cell.
17. **Skills planner JP skill name in display** (S08 finding): "Steadfast 食い下がり" leaks source data.
18. **Skills planner Type/Hints filters disabled** (S08 finding): inert controls.
19. **Skills planner "1 skills" pluralization** (S08 finding).
20. **Events screen "Record event" enabled with empty required fields** (S09 finding).
21. **Events/Skills disclosure caret does not flip** (S08/S09 finding).
22. **Inheritance screen truncated sub-copy** (S10 finding).
23. **Timeline sub-copy truncated** (S12 finding).
24. **Deck screen ownership workflow friction** (S11 finding): no bulk set, no source deck reference.
25. **Deck screen "Save deck" replaces whole deck** (S11 finding): no partial update.

## 7.8 Coverage statement (final)

The client's frames cover the **in-career** screens (`Career`, `Sparks`, `Perks`, `Learn`, `Log`, the in-career rail) and the five `Career Profile` panels (class ladder, goals, trainee details, featured cards, Unity Cup available), plus the Senior-year endgame (race card, training tile, Team Info, roster). The **pre-career setup flow and turn 0 are unfilmed**, as are the preseason rounds' own screens and the finals' race cards. Consequently **every setup-path claim in this audit is source-transcribed or publisher-derived, not frame-observed**; where the current document and a frame disagree, the frame wins and the disagreement is logged as an open item (§2.4).

This walk visited all nine career-nav screens (Cockpit, Training, Races, Skills, Events, Inheritance, Deck, Timeline, Result) and the four snapshot-import screens. The in-career `Career`, `Sparks`, `Perks`, `Learn`, `Log`, and rail are client frames not this application's screens; the `Career Profile` panels are client frames. The application's in-career surfaces are the cockpit and the eight decision screens. All eight decision screens were visited. The cockpit was visited. Coverage of the application's own in-career surfaces is **complete**.

## 7.9 Contamination statement (final)

Read as **context only, never as UI evidence**: the source document's tool-internal citations (`support_cards` rows 30003/30016/30018/30019/30020/30030, `SupportCard::isScenarioLink('unity_cup')`, `config('scenarios.php')`, `CareerTraineeSelectTest`, `database/seeders/data/gametora-characters.e9e9ee6d.json`, the deck table's catalogue / "Client rows visible" columns, and the "D8 of the UX walk remediation" / `AUDIT-AND-VERIFICATION.md` references); and this repository's own prior trail (walks 1–6, the round 2–6 registers, `ui-ux-application-context-brief.md`, `flows-*.md`, `rice-shower-unity-cup-ux-walk-resolutions.md`, `KNOWN-ISSUES.md`, `docs/framework-ui-ux/`, and the peer-landed `rice-shower-unity-cup-round6-reconciliation.md` / `rice-shower-unity-cup-round6-slice-audits.md`). Where a prior finding reproduces, this audit gives its own observation; where it does not, that is recorded as a finding in its own right.